<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:52
 * @copyright Certic (c) 2017
 */

namespace Oscar\Service;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Moment\Moment;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityNotification;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Authentification;
use Oscar\Entity\EstimatedSpentLine;
use Oscar\Entity\Notification;
use Oscar\Entity\NotificationPerson;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\SpentLine;
use Oscar\Entity\SpentTypeGroup;
use Oscar\Entity\SpentTypeGroupRepository;
use Oscar\Entity\ValidationPeriod;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Utils\StringUtils;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Log\Logger;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class SpentService implements UseLoggerService, UseOscarConfigurationService, UseEntityManager
{
    use UseEntityManagerTrait, UseOscarConfigurationServiceTrait, UseLoggerServiceTrait;


    public function getAllArray()
    {
        $array = [];
        /** @var SpentTypeGroup $spendTypeGroup */
        foreach ($this->getSpentTypeRepository()->getAll() as $spendTypeGroup) {
            $array[] = $spendTypeGroup->toJson();
        }
        return $array;
    }

    public function getSpentTypeById($id)
    {
        return $this->getSpentTypeRepository()->find($id);
    }

    public function getAccountsUsed()
    {
        $out = [];

        $sql = 'SELECT DISTINCT comptegeneral FROM spentline ORDER BY comptegeneral::varchar ';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        $used = $stmt->fetchAll();

        $masses = $this->getMasses();
        $i = 0;
        foreach ($used as $compte) {

            $compteInfos = $this->getCompte($compte['comptegeneral']);
            $compteMasse = $compteInfos['annexe'];
            $compteCode = strval($compteInfos['code']);
            $compteLabel = $compteInfos['label'];
            $compteInherit = $compteInfos['masse_inherit'];
            $masseInherit = $compteInfos['compte_inherit'];
            if( !$compteMasse )
                $compteMasse = $compteInherit;

            if (!array_key_exists($compteMasse, $out)) {
                $out[$compteMasse] = [
                    'code' => $compteMasse,
                    'label' => array_key_exists($compteMasse, $masses) ? $masses[$compteMasse] : 'N.D.',
                    'comptes' => []
                ];
            }

            if (!array_key_exists($compteCode, $out[$compteMasse]['comptes'])) {
                $out[$compteMasse]['comptes'][$compteCode] = [
                    'code' => $compteCode,
                    'label' => $compteLabel,
                    'annexe' => $compteMasse,
                    'compte_inherit' => $compteInherit,
                    'masse_inherit' => $masseInherit,
                ];
            }
        }
//        echo "Cd\tMs\tLP\tAP\n";
//        foreach ($out as $m=>$dt) {
//            echo "\n#".$dt['label']."\n";
//            foreach ($dt['comptes'] as $c=>$compteDt) {
//                echo " " . $compteDt['code']
//                    ." \t" . $compteDt['annexe']
//                    ." \t-" . $compteDt['compte_inherit']
//                    ." \t-" . $compteDt['masse_inherit']
//                    ."\n";
//            }
//        }
//        die();

        return $out;


//        $accounts = $this->getEntityManager()->getRepository(SpentTypeGroup::class)->findAll();
//        /** @var SpentTypeGroup $account */
//        foreach ($accounts as $account) {
//            $out[$account->getId()] = $account->getCode() . " : " . $account->getLabel();
//        }
        return $out;
    }


    public function orderSpentsByCode()
    {
        $spents = $this->getSpentTypesIndexCode();
        $bound = 1;
        $open = [];
        foreach ($spents as $code => $spent) {
            $openIndex = count($open) - 1;
            if ($openIndex >= 0) {
                /** @var SpentTypeGroup $lastOpen */
                $lastOpen = &$open[$openIndex];
                while (strlen($lastOpen->getCode()) >= strlen($spent->getCode())) {
                    $lastOpen->setRgt($bound++);
                    array_pop($open);
                    $openIndex--;
                    if ($openIndex < 0) {
                        break;
                    }
                    $lastOpen = $open[$openIndex];

                }
            }
            $spent->setLft($bound++);
            $open[] = $spent;

        }

        while (count($open) > 0) {
            $open[count($open) - 1]->setRgt($bound++);
            array_pop($open);
        }

        foreach ($spents as $code => &$spent) {
            echo $spent . "<br>";
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @return SpentTypeGroup[]
     */
    public function getSpentTypesIndexCode()
    {
        $spents = [];
        /** @var SpentTypeGroup $spent */
        foreach ($this->getSpentTypeRepository()->findBy([], ['code' => 'ASC']) as $spent) {
            $spents[$spent->getCode()] = $spent;
        }
        return $spents;
    }

    public function createSpentTypeGroup($datas)
    {
        $this->checkDatas($datas);

        $label = $datas['label'];
        $code = $datas['code'];
        $annexe = $datas['annexe'];
        $description = $datas['description'];

        $type = new SpentTypeGroup();
        $type->setLabel($label)
            ->setAnnexe($annexe)
            ->setCode($code)
            ->setDescription($description);

        $inside = $datas['inside'];
        if ($inside == 'root') {
            // todo : Récupération du dernier noeud

            $last = $this->getSpentTypeRepository()->getLastSpentTypeGroup();

            if ($last) {
                $lgt = $last->getRgt() + 1;
                $rgt = $lgt + 1;
            } else {
                $lgt = 1;
                $rgt = $lgt + 1;
            }

            $type->setLft($lgt)->setRgt($rgt);
            $this->getEntityManager()->persist($type);
            $this->getEntityManager()->flush($type);
            return $type;
        } else {
            $insiderId = intval($inside);
            if ($insiderId < 1) throw new OscarException(_("DATA ERROR : Type de destination incohérent"));

            // Récupération du noeud racine
            /** @var SpentTypeGroup $insider */
            $insider = $this->getSpentTypeRepository()->find($insiderId);

            if (!$insider) throw new OscarException(_("Impossible de localiser l'emplacement pour le nouveau type"));

            $lgt = $insider->getRgt();
            $rgt = $lgt + 1;

            // Mise à jour des bornes
            $this->getEntityManager()->createNativeQuery(
                'UPDATE spenttypegroup SET lft = lft+2 WHERE lft > :lft', new ResultSetMapping()
            )->execute(['lft' => $lgt]);

            $this->getEntityManager()->createNativeQuery(
                'UPDATE spenttypegroup SET rgt = rgt+2 WHERE rgt >= :rgt', new ResultSetMapping()
            )->execute(['rgt' => $lgt]);

            $type->setLft($lgt)->setRgt($rgt);
            $this->getEntityManager()->persist($type);
            $this->getEntityManager()->flush($type);
            return $type;
        }
    }

    public function getSpentGroupNodeData($userInput)
    {
        $instance = null;

        if (is_object($userInput)) {
            if ($userInput instanceof SpentTypeGroup) {
                $instance = $userInput;
            } else {
                throw new OscarException("Mauvaise entrée utilisateur, impossible de traiter ce type de donnée");
            }
        }

        if ($userInput == "root") {
            return [
                'id' => 'root',
                'label' => 'root',
                'description' => 'root',
                'lft' => 0,
                'rgt' => $this->getSpentTypeRepository()->count() * 2
            ];
        }

        $spentGroup = $this->getSpentTypeRepository()->find($userInput);
        if ($spentGroup) {
            $instance = $spentGroup;
        } else {
            throw new OscarException("Le type de dépense n'a pas été trouvé");
        }

        return [
            'id' => $instance->getId(),
            'label' => $instance->getLabel(),
            'description' => $instance->getDescription(),
            'lft' => $instance->getLft(),
            'rgt' => $instance->getRgt()
        ];
    }

    public function deleteNode($spent, $deleteEntity = true)
    {
        $lft = $spent['lft'];
        $rgt = $spent['rgt'];
        $decalage = $rgt - $lft + 1;

        if ($deleteEntity === true) {
            // Suppression
            $this->getEntityManager()->createNativeQuery(
                'DELETE FROM spenttypegroup WHERE lft >= :lft AND rgt <= :rgt',
                new ResultSetMapping()
            )->execute(['lft' => $lft, 'rgt' => $rgt]);
        }

        // Mise à jour des bornes
        $this->getEntityManager()->createNativeQuery(
            'UPDATE spenttypegroup SET rgt = rgt - :decalage WHERE rgt > :rgt', new ResultSetMapping()
        )->execute(['decalage' => $decalage, 'rgt' => $rgt, 'lft' => $lft]);

        $this->getEntityManager()->createNativeQuery(
            'UPDATE spenttypegroup SET lft = lft - :decalage WHERE lft >= :lft', new ResultSetMapping()
        )->execute(['decalage' => $decalage, 'rgt' => $rgt, 'lft' => $lft]);
    }

    public function moved($movedId, $destination)
    {

        /** @var SpentTypeGroup $move */
        $move = $this->getSpentTypeRepository()->find($movedId);

        $sizeBranch = $move->getRgt() - $move->getLft() + 1;

        $this->getLogger()->debug("Taille de la branche : $sizeBranch");

        $borneMovedLeft = $move->getLft();
        $borneMovedRight = $move->getRgt();

        $this->getEntityManager()->createNativeQuery(
            'UPDATE spenttypegroup SET lft = lft - :rgt, rgt = rgt - :rgt WHERE lft >= :lft AND rgt <= :rgt', new ResultSetMapping()
        )->execute(['lft' => $borneMovedLeft, 'rgt' => $borneMovedRight]);


        $this->getEntityManager()->createNativeQuery(
            'UPDATE spenttypegroup SET lft = lft - :size, rgt = rgt - :size WHERE lft >= :lft', new ResultSetMapping()
        )->execute(['lft' => $borneMovedRight, 'size' => $sizeBranch]);


        /** @var SpentTypeGroup dest */
        $dest = $this->getSpentTypeRepository()->find($destination);

        $destPoint = $dest->getLft() + 1;
        $this->getLogger()->debug("Point d'entrée : $destPoint");

        // TROU
        $this->getEntityManager()->createNativeQuery(
            'UPDATE spenttypegroup SET lft = lft + :size WHERE lft >= :lft', new ResultSetMapping()
        )->execute(['lft' => $destPoint, 'size' => $sizeBranch]);

        $this->getEntityManager()->createNativeQuery(
            'UPDATE spenttypegroup SET rgt = rgt + :size WHERE rgt >= :rgt', new ResultSetMapping()
        )->execute(['rgt' => $destPoint, 'size' => $sizeBranch]);

        // ON replace la branche
        $deplacement = $sizeBranch + $destPoint - 1;
        $this->getEntityManager()->createNativeQuery(
            'UPDATE spenttypegroup SET lft = lft + :size, rgt = rgt + :size WHERE rgt <= :pos', new ResultSetMapping()
        )->execute(['size' => $deplacement, 'pos' => 0]);
    }


    public function getYearsListActivity(Activity $activity)
    {

        if (!$activity->getDateStart())
            throw new OscarException(sprintf(_("L'activité %s n'a pas de date de début"), $activity));

        if (!$activity->getDateEnd())
            throw new OscarException(sprintf(_("L'activité %s n'a pas de date de fin"), $activity));

        if ($activity->getDateEnd() < $activity->getDateStart()) {
            throw new OscarException(sprintf(_("L'activité %s a une date de fin antérieur à sa date de début"), $activity));
        }

        $yearStart = (int)$activity->getDateStart()->format('Y');
        $yearEnd = (int)$activity->getDateEnd()->format('Y');

        $years = [];
        for ($i = $yearStart; $i <= $yearEnd; $i++) {
            $years[] = $i;
        }

        return $years;
    }

    /**
     * Retourne la liste des lignes de budget regroupées par masse
     * @return array
     */
    public function getLinesByMasse()
    {
        $query = $this->getSpentTypeRepository()->createQueryBuilder('s')
            ->orderBy('s.annexe', 'ASC')
            ->addOrderBy('s.code', 'ASC')
            ->where("s.annexe != ''");

        $result = $query->getQuery()->getArrayResult();
        return $result;
    }

    public function getMasses()
    {
        return $this->getOscarConfigurationService()->getConfiguration('spenttypeannexes');
    }

    public function getTypesTree()
    {

        $types = $this->getSpentTypeRepository()->getAll();
        $root = [
            'label' => 'root',
            'lft' => 0,
            // 'corpus' => '',
            'rgt' => count($types) * 2 + 1,
            'empty' => true,
            'children' => []
        ];

        $parents = [$root];
        $typesArray = [];


        /** @var SpentTypeGroup $type */
        foreach ($types as $type) {
            $item = $type->toJson();
            // $item['corpus'] = $item['label'];
            $item['empty'] = $type->getAnnexe() == '';
            $l = $type->getLft();
            $r = $type->getRgt();

            $lastParent = &$parents[count($parents) - 1];

            while ($r > $lastParent['rgt']) {
                $child = array_pop($parents);
                // $parents[count($parents)-1]['corpus'] .= $child['corpus'];
                $parents[count($parents) - 1]['children'][] = $child;
                if ($child['empty'] == false) {
                    $parents[count($parents) - 1]['children'][] = false;
                }
                $lastParent = &$parents[count($parents) - 1];
            }

            if ($r > $l + 1) {
                $item['children'] = [];
                $parents[] = $item;
            } else {
                $item['parent_id'] = $lastParent['id'];
                $lastParent['children'][] = $item;
                // $lastParent['corpus'] .= $item['corpus'] . ' ';
                if ($item['empty'] == false) {
                    //die($item['code']);
                    foreach ($parents as &$p) {
                        $p['empty'] = false;
                    }
                }

            }
        }

        while (count($parents) > 1) {
            $child = array_pop($parents);
            // $parents[count($parents) - 1]['corpus'] .= $child['corpus'];
            $parents[count($parents) - 1]['children'][] = $child;
            if ($child['empty'] == false) {
                $parents[count($parents) - 1]['children'][] = false;
            }
        }

        return $parents[0];
    }

    public function loadPCG()
    {
        $filepath = $this->getOscarConfigurationService()->getConfiguration('spenttypesource');

        $spentTypes = $this->getSpentTypesIndexCode();

        $re = '/(\d+)\.?/';

        if (($handle = fopen($filepath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                if (preg_match($re, $data[0], $matches)) {
                    $code = $matches[1];
                    $label = $data[1];
                } else if (preg_match($re, $data[1], $matches)) {
                    $code = $matches[1];
                    $label = $data[2];
                } else if (preg_match($re, $data[2], $matches)) {
                    $code = $matches[1];
                    $label = $data[3];
                } else if (preg_match($re, $data[3], $matches)) {
                    $code = $matches[1];
                    $label = $data[4];
                } else {
                    continue;
                }

                if (array_key_exists($code, $spentTypes)) {

                } else {
                    $spentType = new SpentTypeGroup();
                    $this->getEntityManager()->persist($spentType);
                    $spentType->setCode($code)->setLabel($label)->setLft(1)->setRgt(2)->setDescription("");
                    $this->getEntityManager()->flush($spentType);
                }
            }
            fclose($handle);
        }

        $this->orderSpentsByCode();

    }


    public function admin()
    {

        $i = 0;
        /** @var SpentTypeGroup $node */
        foreach ($this->getSpentTypeRepository()->findAll() as $node) {
            $node->setLft(++$i)->setRgt(++$i);
        }
        $this->getEntityManager()->flush();

    }

    public function updateSpentTypeGroup($datas)
    {
        $this->checkDatas($datas);

        $id = intval($datas['id']);

        if ($id < 1) {
            throw new OscarException(_("Impossible de trouver le type de dépense à mettre à jour"));
        }

        /** @var SpentTypeGroup $spentTypeGroup */
        $spentTypeGroup = $this->getSpentTypeRepository()->find($id);

        if (!$spentTypeGroup) {
            throw new OscarException(_("Le type de dépense n'a pas été trouvé"));
        }

        $label = $datas['label'];
        $code = $datas['code'];
        $annexe = $datas['annexe'];
        $description = $datas['description'];

        if (array_key_exists('inside', $datas)) {
            $inside = $datas['inside'];
        }

        $spentTypeGroup->setLabel($label)
            ->setDescription($description)
            ->setAnnexe($annexe)
            ->setCode($code);

        $this->getEntityManager()->flush($spentTypeGroup);
    }

    /**
     * @return SpentTypeGroupRepository
     */
    protected function getSpentTypeRepository()
    {
        return $this->getEntityManager()->getRepository(SpentTypeGroup::class);
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->getLoggerService();
    }

    /**
     * Vérification des données reçues depuis la saisie utilisateur.
     *
     * @param $datas
     * @throws OscarException
     */
    protected function checkDatas($datas)
    {
        if (!$datas['label']) {
            throw new OscarException(_("Vous devez renseigner un intitulé."));
        }
        if (!$datas['code']) {
            throw new OscarException(_("Le champ code doit être renseigné"));
        }
    }

    private $_cacheCompte = [];
    private $cachePlan;

    public function getPlanComptable()
    {
        static $cachePlanComptableCG;
        if ($cachePlanComptableCG == null) {
            $out = [];
            $plan = $this->getEntityManager()->getRepository(SpentTypeGroup::class)->findAll();
            /** @var SpentTypeGroup $l */
            foreach ($plan as $l) {
                $out['00' . StringUtils::feedString($l->getCode())] = $l;
            }
            return $out;
        }
        return $cachePlanComptableCG;
    }

    protected function getParent($plan, $codeEnfant){
        if( strlen($codeEnfant) <= 1 ){
            return [
                'parentCode' => '',
                'parentLabel' => '',
                'parentMasse' => ''
            ];
        }

        $codeParent = substr($codeEnfant, 0, strlen($codeEnfant)-1);
        $indexParent = '00' . StringUtils::feedString($codeParent);

        if( array_key_exists($indexParent, $plan) ){
            $parent = $plan[$indexParent];
            $labelParent = $parent->getLabel();
            $masseParent = $parent->getAnnexe();
            if( $masseParent ){
                return [
                    'parentCode' => $codeParent,
                    'parentLabel' => $labelParent,
                    'parentMasse' => $masseParent
                ];
            }
        }
        return $this->getParent($plan, $codeParent);
    }

    public function getCompte($code)
    {

        static $cacheCompte;


        if ($cacheCompte == null) {
            $cacheCompte = [];
        }

        if (!array_key_exists($code, $cacheCompte)) {

            $plan = $this->getPlanComptable();
            $find = null;
            $reduce = strval($code);

            $out = [];

            for ($i = strlen($reduce) - 1; $find == null && $i > 0; $i--) {
                if (array_key_exists($reduce, $plan)) {
                    $parent = $this->getParent($plan, $plan[$reduce]->getCode());
                    $out['label'] = $plan[$reduce]->getLabel();
                    $out['code'] = $plan[$reduce]->getCode();
                    $out['annexe'] = $plan[$reduce]->getAnnexe();
                    $out['masse_inherit'] = $parent['parentMasse'];
                    $out['compte_inherit'] = $parent['parentCode'];
                    $find = $out;
                }
                $reduce[$i] = '0';
            }

            if ($find == null) {
                $cacheCompte[$code] = [
                    'label' => '',
                    'code' => '',
                    'annexe' => ''
                ];
            } else {
                $cacheCompte[$code] = $out;
            }
        }

        return $cacheCompte[$code];
    }

    public function getSpentsByPFI($pfi)
    {
        $qb = $this->getEntityManager()->getRepository(SpentLine::class)->createQueryBuilder('s');
        $qb->where('s.pfi = :pfi');
        $qb->orderBy('s.datePaiement', 'ASC');

        $filtreCompte = $this->getOscarConfigurationService()->getSpentAccountFilter();

        if ($filtreCompte) {
            $qb->andWhere('s.compteBudgetaire NOT IN(:filtreCompte)')
                ->setParameter('filtreCompte', $filtreCompte);
        }

        return $qb->getQuery()->setParameter('pfi', $pfi)->getResult();
    }

    /**
     * Retourne les données de synthèse des dépenses pour un PFI donné sous la forme d'un tableau :
     * [
     *  'masse1'    => float,
     *  'masse2'    => float,
     *  'masseN'    => float,
     *  'N.B'       => float,
     *  'total'     => float
     * ]
     *
     * @param $pfi
     */
    public function getSynthesisDatasPFI($pfi)
    {

        // Récupération des dépenses
        $spents = $this->getSpentsByPFI($pfi);

        // Récupération des Masses comptable configurées dans config
        $masses = $this->getOscarConfigurationService()->getMasses();

        // Structuration du tableau de retour
        $out = [];

        $out['N.B'] = 0.0;
        $out['entries'] = count($spents);
        $out['total'] = 0.0;
        $out['details'] = [];
        $out['totals'] = [];
        $out['totals']['N.B'] = 0.0;
        $out['details']['N.B'] = [];

        foreach ($masses as $key => $label) {
            $out[$key] = 0.0;
            $out['totals'][$key] = 0.0;
            $out['details'][$key] = [];
        }

        // Aggrégation des données
        /** @var SpentLine $spent */
        foreach ($spents as $spent) {
            $compte = $spent->getCompteGeneral();
            $compteInfos = $this->getCompte($compte);
            $annexe = $compteInfos['annexe'];

            if ($annexe == '') {
                $annexe = 'N.B';
                if (!in_array($compte, $out['details'][$annexe]))
                    $out['details'][$annexe][] = $compte . ' (' . $compteInfos['label'] . ')';
            }
            $out[$annexe] += floatval($spent->getMontant());
            $out['total'] += floatval($spent->getMontant());
            $out['totals'][$annexe] += floatval($spent->getMontant());
        }
        return $out;
    }

    public function getSpentsTypes()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from(SpentTypeGroup::class, 't', 't.code');
        //$qb = $this->getEntityManager()->getRepository(SpentTypeGroup::class)->createQueryBuilder('t')->indexBy('t.code', 'code');
        return $qb->getQuery()->getArrayResult();
    }

    public function syncSpentsByEOTP($eotp)
    {
        if (!$eotp) {
            throw new OscarException("Pas d'EOTP");
        }

        // TODO (optimiser ça avec une requête native propre)
        $activities = $this->getEntityManager()->getRepository(Activity::class)->findBy([
            'codeEOTP' => $eotp
        ]);

        if (count($activities) > 0) {
            $spents = $this->getSpentsByPFI($eotp);
            $total = 0.0;
            /** @var SpentLine $spent */
            foreach ($spents as $spent) {
                $total += (floatval($spent->getMontant()));
            }

            /** @var Activity $activity */
            foreach ($activities as $activity) {
                $activity->setTotalSpent($total);
                $activity->setDateTotalSpent(new \DateTime());
            }

            $this->getEntityManager()->flush($activities);
        }

        return $this->getConnector()->sync($eotp);
    }

    protected function reduceZero($str)
    {
        $r = intval(strrev($str));
        $rstr = "" . intval($r);
        return intval(strrev($rstr));
    }

    protected function getNearestType($code, $original = "")
    {
        static $types;
        static $assoc;

        // Fix : Pas de type chargé en base de donnée
        if ($code === false) return "0";

        if ($types === null)
            $types = $this->getSpentsTypes();

        if ($assoc === null) {
            $assoc = [];
        }

        $typeInt = $this->reduceZero($code);
        if (array_key_exists($typeInt, $types)) {
            return $types[$typeInt]['label'] . ($original ? sprintf(' (%s)', $original) : '');
        } else {
            $base = $original ? $original : $code;
            $reduceCode = substr($code, 0, strlen($code) - 1);
            return $this->getNearestType($reduceCode, $base);
        }
    }

    protected function getTypeByCode($code)
    {
        static $assoc;
        if ($assoc == null) {
            $assoc = [];
        }

        if (!array_key_exists($code, $assoc)) {
            $assoc[$code] = $this->getNearestType($code);
        }

        return $assoc[$code];
    }

    public function getDatasActivitiesSpents()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('a.id', 'a.codeEOTP', 'a.label')
            ->from(Activity::class, 'a')
            ->where('a.codeEOTP IS NOT NULL AND a.codeEOTP != \'\'');

        $total = 0;

        foreach ($qb->getQuery()->getArrayResult() as $row) {
            $total++;
            echo $row['id'] . "\t"
                . ' [' . $row['codeEOTP'] . "]\t"
                . ' ' . substr($row['label'], 0, 20)
                . "\n";
        }
        echo "Total : $total";
    }

    public function getPFIList()
    {
        $qb = $this->getEntityManager()->createQueryBuilder('a')
            ->select('DISTINCT a.codeEOTP')
            ->where('a.codeEOTP IS NOT NULL')
            ->from(Activity::class, 'a');
        return array_column($qb->getQuery()->getArrayResult(), 'codeEOTP');
    }

    public function getSpentsSyncIdByPFI($pfi)
    {
        $qb = $this->getEntityManager()->createQueryBuilder('s')
            ->select('s.syncId')
            ->from(SpentLine::class, 's')
            ->where('s.pfi = :pfi')
            ->setParameter('pfi', $pfi);
        return array_column($qb->getQuery()->getArrayResult(), 'syncId');
    }

    public function getGroupedSpentsDatas($pfi)
    {
        $re = '/^(0*)([0-9]*)$/m';
        $spents = $this->getSpentsByPFI($pfi);
        $out = [];
        $grouped = [];
        /** @var SpentLine $spent */
        foreach ($spents as $spent) {

            $numPiece = $spent->getNumPiece();
            $compteBudg = $spent->getCompteBudgetaire();
            $compte = $this->getCompte($spent->getCompteGeneral());
            $masse = $compte['annexe'];

            $type = $this->getTypeByCode($spent->getCompteGeneral());


            if (!array_key_exists($numPiece, $grouped)) {
                $grouped[$numPiece] = [
                    'ids' => [],
                    'syncIds' => [],
                    'text' => [],
                    'types' => [],
                    'montant' => 0.0,
                    'compteBudgetaire' => [],
                    'compteGenerale' => [],
                    'masse' => [],
                    'datecomptable' => $spent->getDateComptable(),
                    'datepaiement' => $spent->getDatePaiement(),
                    'annee' => $spent->getDateAnneeExercice(),
                    'refPiece' => $spent->getPieceRef(),
                    'details' => []
                ];
            }

            if ($compteBudg == 'PG_REM') {
                $grouped[$numPiece]['refPiece'] = $spent->getPieceRef();
            }

            if ($spent->getDesignation() && !in_array($spent->getDesignation(), $grouped[$numPiece]['text'])) {
                $grouped[$numPiece]['text'][] = $spent->getDesignation();
            }

            if ($spent->getTexteFacture() && !in_array($spent->getTexteFacture(), $grouped[$numPiece]['text'])) {
                $grouped[$numPiece]['text'][] = $spent->getTexteFacture();
            }

            if ($spent->getCompteBudgetaire() && !in_array($spent->getCompteBudgetaire(), $grouped[$numPiece]['compteBudgetaire'])) {
                $grouped[$numPiece]['compteBudgetaire'][] = $spent->getCompteBudgetaire();
            }

            if ($spent->getCompteGeneral() && !in_array($spent->getCompteGeneral(), $grouped[$numPiece]['compteGenerale'])) {
                $grouped[$numPiece]['compteGenerale'][] = $spent->getCompteGeneral();
                if (!in_array($masse, $grouped[$numPiece]['masse'])) {
                    $grouped[$numPiece]['masse'][] = $masse;
                }
            }

            $grouped[$numPiece]['ids'][] = $spent->getId();
            $grouped[$numPiece]['syncIds'][] = $spent->getSyncId();
            $grouped[$numPiece]['types'][] = $type;
            $grouped[$numPiece]['montant'] += $spent->getMontant();

            $details = $spent->toArray();
            $details['annexe'] = $masse;
            $details['compteGeneralLabel'] = $compte['label'];
            $details['codeStr'] = $type;
            $grouped[$numPiece]['details'][] = $details;
        }

        return $grouped;
    }

    public function getPrevisionnalSpentsByPfi($pfi, $justValue = false)
    {
        $out = [];

        $query = $this->getEntityManager()->getRepository(EstimatedSpentLine::class)
            ->createQueryBuilder('e')
            ->where('e.pfi = :pfi')
            ->getQuery();

        $estimatedSpents = $query->setParameter('pfi', $pfi)->getResult();

        /** @var EstimatedSpentLine $estimatedSpent */
        foreach ($estimatedSpents as $estimatedSpent) {
            $account = (string)$estimatedSpent->getAccount();
            $year = (string)$estimatedSpent->getYear();
            if (!array_key_exists($account, $out)) {
                $out[$account] = [];
            }
            $out[$account][$year] = $justValue ? $estimatedSpent->getAmount() : $estimatedSpent;
        }

        return $out;
    }

    public function getConnector()
    {
        $oscarConfig = $this->getOscarConfigurationService();
        $connectorConfig = $oscarConfig->getConfiguration('connectors.spent');
        $keysConfig = array_keys($connectorConfig);

        if (count($keysConfig) == 0) {
            throw new OscarException("Pas de synchronisation des dépenses configuré");
        } elseif (count($keysConfig) > 1) {
            throw new OscarException("Oscar ne prends en charge qu'une source de synchronisation pour les dépenses.");
        } else {

            $conf = $connectorConfig[$keysConfig[0]];
            $class = $conf['class'];
            $factory = new \ReflectionClass($class);

            /** @var ConnectorSpentSifacOCI $instance */
            $instance = $factory->newInstanceArgs([$this, $conf['params']]);
            return $instance;
        }
    }


    public function addSpentLine(array $data)
    {
        $spentLine = new SpentLine();
        $this->getEntityManager()->persist($spentLine);

        $spentLine->setSyncId($data['IDSYNC']);
        $spentLine->setPfi($data['PFI']);
        $spentLine->setNumSifac($data['NUMSIFAC']);
        $spentLine->setNumCommandeAff($data['NUMCOMMANDEAFF']);
        $spentLine->setNumPiece($data['NUMPIECE']);
        $spentLine->setNumFournisseur($data['NUMFOURNISSEUR']);
        $spentLine->setPieceRef($data['PIECEREF']);
        $spentLine->setCodeSociete($data['CODESOCIETE']);
        $spentLine->setCodeServiceFait($data['CODESERVICEFAIT']);
        $spentLine->setCodeDomaineFonct($data['CODEDOMAINEFONCT']);
        $spentLine->setDesignation($data['DESIGNATION']);
        $spentLine->setTexteFacture($data['TEXTEFACTURE']);
        $spentLine->setTypeDocument($data['TYPEDOCUMENT']);
        $spentLine->setMontant($data['MONTANT']);
        $spentLine->setCentreDeProfit($data['CENTREDEPROFIT']);
        $spentLine->setCompteBudgetaire($data['COMPTEBUDGETAIRE']);
        $spentLine->setCentreFinancier($data['CENTREFINANCIER']);
        $spentLine->setCompteGeneral($data['COMPTEGENERAL']);
        $spentLine->setDatePiece($data['DATEPIECE']);
        $spentLine->setDateComptable($data['DATECOMPTABLE']);
        $spentLine->setDateAnneeExercice($data['DATEANNEEEXERCICE']);
        $spentLine->setDatePaiement($data['DATEPAIEMENT']);
        $spentLine->setDateServiceFait($data['DATESERVICEFAIT']);

        $this->getEntityManager()->flush($spentLine);
    }
}