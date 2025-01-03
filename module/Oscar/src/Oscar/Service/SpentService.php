<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:52
 * @copyright Certic (c) 2017
 */

namespace Oscar\Service;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\ResultSetMapping;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityRepository;
use Oscar\Entity\EstimatedSpentLine;
use Oscar\Entity\SpentLine;
use Oscar\Entity\SpentTypeGroup;
use Oscar\Entity\SpentTypeGroupRepository;
use Oscar\Exception\OscarException;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Utils\AccountInfoUtil;
use Oscar\Utils\StringUtils;
use Laminas\Log\Logger;


class SpentService implements UseLoggerService, UseOscarConfigurationService, UseEntityManager
{
    use UseEntityManagerTrait, UseOscarConfigurationServiceTrait, UseLoggerServiceTrait;


    /**
     * @return array
     * @throws NotSupported
     */
    public function getAllArray()
    {
        $array = [];
        /** @var SpentTypeGroup $spendTypeGroup */
        foreach ($this->getSpentTypeRepository()->getAll() as $spendTypeGroup) {
            $array[] = $spendTypeGroup->toJson();
        }
        return $array;
    }

    /**
     * @param $id
     * @return object|null
     * @throws NotSupported
     */
    public function getSpentTypeById($id)
    {
        return $this->getSpentTypeRepository()->find($id);
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAccountsUsed() :array
    {
        $out = [];

        try {
            // On test si il y'a des comptes
            $sql = 'SELECT DISTINCT comptegeneral FROM spentline ORDER BY comptegeneral';
            $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
            $used = $stmt->executeQuery()->fetchAllAssociative();
            $masses = $this->getMasses();

            foreach ($used as $compte) {
                $compteInfos = $this->getCompte($compte['comptegeneral']);
                $compteMasse = $compteInfos['annexe'];
                $compteCode = strval($compteInfos['code']);
                $compteLabel = $compteInfos['label'];
                $compteInherit = $compteInfos['compte_inherit'];
                $masseInherit = $compteInfos['masse_inherit'];
                if( !$compteMasse )
                    $compteMasse = $masseInherit;

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
        } catch (\Exception $e) {
            $msg = "Erreur lors de la récupération des comptes";
            $this->getLogger()->err("$msg : " . $e->getMessage());
            throw new OscarException($msg);
        }

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
    public function getSpentTypesIndexCode(): array
    {
        $spents = [];
        /** @var SpentTypeGroup $spent */
        foreach ($this->getSpentTypeRepository()->findBy([], ['code' => 'ASC']) as $spent) {
            $spents[$spent->getCode()] = $spent;
        }
        return $spents;
    }

    public function createSpentTypeGroup($datas): SpentTypeGroup
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
            ->orderBy('s.lft', 'ASC')
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

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return SpentTypeGroupRepository
     * @throws NotSupported
     */
    public function getSpentTypeRepository()
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
            $cachePlanComptableCG = [];
            $plan = $this->getEntityManager()->getRepository(SpentTypeGroup::class)->findAll();
            /** @var SpentTypeGroup $l */
            foreach ($plan as $l) {
                $cachePlanComptableCG['00' . StringUtils::feedString($l->getCode())] = $l;
            }
        }
        return $cachePlanComptableCG;
    }

    /**
     * Retourne le premier parent avec une annexe renseignée.
     *
     * @param $plan
     * @param $codeEnfant
     * @return array
     */
    protected function getParentWithAnnexe($plan, $codeEnfant){
        $indexInPlan = '00'.StringUtils::feedString($codeEnfant);
        $parentCode = '';
        $parentlabel = '';
        $parentmasse = '';

        // Si on est au dernier niveau
        if( strlen($codeEnfant) <= 1 ){
            if( array_key_exists($indexInPlan, $plan) ){
                $parentCode = $plan[$indexInPlan]->getCode();
                $parentlabel = $plan[$indexInPlan]->getLabel();
                $parentmasse = $plan[$indexInPlan]->getAnnexe();
            }
            return [
                'parentCode' => $parentCode,
                'parentLabel' => $parentlabel,
                'parentMasse' => $parentmasse
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
        return $this->getParentWithAnnexe($plan, $codeParent);
    }

    public function getIdsActivitiesForCompteGeneral( array $compteGeneral ) :array
    {
        $pfis = $this->getSpentTypeRepository()->getPfiForCodesAccounts($compteGeneral);
        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->getEntityManager()->getRepository(Activity::class);
        $idsActivities = $activityRepository->getActivitiesIdsByPfis($pfis);
        return $idsActivities;
    }

    public function getIdsActivitiesForAccounts( array $codesAccounts ) :array
    {
        $pfis = $this->getSpentTypeRepository()->getPfiForCodesAccounts($codesAccounts);

        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->getEntityManager()->getRepository(Activity::class);
        $idsActivities = $activityRepository->getActivitiesIdsByPfis($pfis);
        return $idsActivities;
    }

    /**
     * Retourne les informations pour un compte à partir de son code : 00XXXXXXXXXX
     * Pour déterminer l'annexe budgétaire, le code cherche dans le compte, puis
     * remonte dans les parents jusqu'à trouver une annexe.
     *
     * @param $code
     * @return array|mixed|string[]
     */
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
                if( $reduce[$i] == '0' ) continue;
                if (array_key_exists($reduce, $plan)) {
                    $parent = $this->getParentWithAnnexe($plan, $plan[$reduce]->getCode());
                    $out['id'] = $plan[$reduce]->getId();
                    $out['label'] = $plan[$reduce]->getLabel();
                    $out['code'] = $plan[$reduce]->getCode();
                    $out['codeFull'] = $code;
                    $out['annexe'] = $plan[$reduce]->getAnnexe();
                    $out['masse_inherit'] = $parent['parentMasse'];
                    $out['compte_inherit'] = $parent['parentCode'];
                    $find = $out;
                } else {
                    $reduce[$i] = '0';
                }
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


    /**
     * Chargement des lignes de dépense pour les PFI donnés
     *
     * @see getSpentsByPFI
     * @param array $pfis
     * @param $spentType
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSpentsByPFIs(array $pfis, $spentType = self::SPENT_EFFECTIVE){
        $out = [];
        foreach ($pfis as $pfi) {
            $spents = $this->getSpentsByPFI($pfi, $spentType);
            foreach ($spents as $s) {
                $out[] = $s;
            }
        }
        return $out;
    }

    /**
     * Chargement des lignes de dépense pour le PFI donné.
     *
     * @param $pfi
     * @param int $spentType
     * @return float|int|mixed|string
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSpentsByPFI($pfi, int $spentType)
    {
        $qb = $this->getEntityManager()
            ->getRepository(SpentLine::class)
            ->createQueryBuilder('s');

        // Nouvelle requête "Native"
        $queryPG = "SELECT s.id FROM spentline s LEFT JOIN spenttypegroup st ON '00' || rpad(st.code, 8, '0') = s.comptegeneral WHERE s.pfi = '$pfi'";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($queryPG);
        $result = $stmt->executeQuery();
        $ids = array_map('current', $result->fetchAllAssociative()); // Fetch

        $qb->where('s.id IN(:ids)')
            ->orderBy('s.datePaiement', 'ASC')
            ->setParameter('ids', $ids);

        if( $spentType == self::SPENT_EFFECTIVE ){
            $qb->andWhere('s.btart = :btart')
                ->setParameter('btart', '0250');
        }

        if( $spentType == self::SPENT_PREVISIONNAL ){
            $qb->andWhere('s.btart = :btart')
                ->setParameter('btart', '0100');
        }

        $filtreCompte = $this->getOscarConfigurationService()->getSpentAccountFilter();

        if ($filtreCompte) {
            $qb->andWhere('s.compteBudgetaire NOT IN(:filtreCompte)')
                ->setParameter('filtreCompte', $filtreCompte);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Enregistre les affectations des comptes
     *
     * @param $affectations
     * @throws OscarException
     */
    public function updateAffectation( $affectations )
    {
        $dump = [];
        $masses = $this->getOscarConfigurationService()->getMasses();

        foreach ($affectations as $codeCompteFull => $compteAffectation) {
            $infos = $this->getCompte($codeCompteFull);
            if( $infos['code'] ){
                /** @var SpentTypeGroup $spentType */
                $spentType = $this->getSpentTypeRepository()->findOneByCode($infos['code']);
            } else {
                $this->getLoggerService()->error("Le compte $codeCompteFull n'existe pas dans le plan comptable.");
                throw new OscarException("Le compte $codeCompteFull n'existe pas dans le plan comptable.");
            }

            if( $compteAffectation == '1' ){
                // Recette / Ignorer
                $spentType->setBlind(false);
                $spentType->setAnnexe($compteAffectation);
            }
            elseif ( $compteAffectation == '0' ){
                $spentType->setAnnexe('0');
                $spentType->setBlind(true);
            }
            elseif ( $compteAffectation == '' ){
                $this->getLoggerService()->error("Erreur d'affectation pour $codeCompteFull (valeur nulle).");
                throw new OscarException("Erreur d'affectation (valeur nulle)");
            }
            else {
                $spentType->setBlind(false);
                $spentType->setAnnexe($compteAffectation);
            }
            $this->getEntityManager()->flush($spentType);
        }
        return true;
    }

    /**
     * Liste des comptes utilisés.
     *
     * @return array
     */
    public function getUsedAccount()
    {
        $usedAccounts = $this->getSpentTypeRepository()->getUsedAccount();
        $accountInfos = [];
        foreach ($usedAccounts as $compte) {
            $infos = $this->getCompte($compte);
            $accountInfos[] = $infos;
        }
        return $accountInfos;
    }

    public function getAccountsInfosUsed() :AccountInfoUtil
    {
        return AccountInfoUtil::getInstance($this);
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
     * @param false $curationNB
     * @return array
     * @throws Exception
     */
    public function getSynthesisDatasPFI($pfis, $curationNB = false, string $mode = 'advanced' ) :array
    {
        $spents = [];
        foreach ($pfis as $pfi) {
            $spents = array_merge($spents, $this->getSpentsByPFI($pfi, self::SPENT_BOTH));
        }

        // Récupération des Masses configurées dans la configuration
        $masses = $this->getOscarConfigurationService()->getMasses();

        return [
            'pfi' => $pfi,
            'masses' => $masses,
            'synthesis' => $this->getSpentDatasSynthesisBySpents($spents)
        ];
    }

    public function getSpentsTypes()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from(SpentTypeGroup::class, 't', 't.code');
        //$qb = $this->getEntityManager()->getRepository(SpentTypeGroup::class)->createQueryBuilder('t')->indexBy('t.code', 'code');
        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Synchronisation des dépenses via le PFI.
     *
     * @param string|null $eotp
     * @return mixed
     * @throws OscarException
     * @throws Exception
     * @throws NotSupported
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function syncSpentsByEOTP(?string $eotp, bool $optimized = false)
    {
        if (!$eotp) {
            $this->getLoggerService()->error("Aucun PFI fourni");
            throw new OscarException("Pas d'EOTP");
        }

        $this->getLoggerService()->info("Mise à jour des dépenses pour '$eotp'");

        // TODO (optimiser ça avec une requête native propre)
        $activities = $this->getEntityManager()->getRepository(Activity::class)->findBy([
            'codeEOTP' => $eotp
        ]);

        if (count($activities) > 0) {

            // synchro
            try {
                $this->getConnector()->sync($eotp);
            } catch (\Exception $e) {
                throw $e;
            }

            // Récup
            $spents = $this->getSpentsByPFI($eotp, self::SPENT_EFFECTIVE);
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
        } else {
            $this->getLoggerService()->warning("Aucune activité trouvé pour '$eotp'");
        }
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

    public function getLastSyncId(string $pfi)
    {
        $qb = $this->getEntityManager()->createQueryBuilder('s')
            ->select('MAX(s.syncId)')
            ->from(SpentLine::class, 's')
            ->where('s.pfi = :pfi')
            ->setParameter('pfi', $pfi);
        return $qb->getQuery()->getSingleScalarResult();
    }

    const SPENT_EFFECTIVE = 1;
    const SPENT_PREVISIONNAL = 2;
    const SPENT_BOTH = 3;

    /**
     * @param $pfi
     * @return array
     */
    public function getSpentsDatas($pfi, $spentType = self::SPENT_EFFECTIVE){

        $array = [];
        $spents = $this->getSpentsByPFI($pfi, $spentType);
        $comptes = [];



        /** @var SpentLine $spent */
        foreach ( $spents as $spent) {
            $compte = $this->getCompte($spent->getCompteGeneral());
            $comptes[$spent->getCompteGeneral()] = $compte;
            $masse = $masseGroup = $compte['annexe'] != '' ? $compte['annexe'] : $compte['masse_inherit'];
            $type = $this->getTypeByCode($spent->getCompteGeneral());
            $spentArray = $spent->toArray();
            $spentArray['masse'] = $masse;
            $spentArray['compte'] = $compte['code'] . ' : ' . $compte['label'];
            $spentArray['type'] = $type;
            $array[] = $spentArray;
        }
        return [
            'comptes' => $comptes,
            'spents' => $array,
            'masses' => $this->getOscarConfigurationService()->getMasses(),
            'synthesis' => $this->getSpentDatasSynthesisBySpents($spents)
        ];
    }

    /**
     * Retourne la synthèse des dépenses/recettes à partir de la liste des entrées du journal des pièces (SpentLine).
     *
     * @param $spents
     * @return array[]
     */
    public function getSpentDatasSynthesisBySpents($spents){
        $pfis = [];
        $synthesis = [
            'lines' => count($spents),
            '1' => [
                'label' => "Recettes",
                'total' => 0.0,
                'total_engage' => 0.0,
                'total_effectue' => 0.0,
                'nbr_engage' => 0,
                'nbr_effectue' => 0,
                'nbr' => 0
            ],
            '0' => [
                'label' => "Ignorés",
                'total' => 0.0,
                'total_engage' => 0.0,
                'total_effectue' => 0.0,
                'nbr_engage' => 0,
                'nbr_effectue' => 0,
                'nbr' => 0
            ],
            'N.B' => [
                'label' => "Hors-Masse",
                'total' => 0.0,
                'total_engage' => 0.0,
                'total_effectue' => 0.0,
                'nbr_engage' => 0,
                'nbr_effectue' => 0,
                'nbr' => 0
            ],
            'totaux' => [
                'engage' => 0.0,
                'effectue' => 0.0,
            ]
        ];

        $massesKeys = array_keys($this->getOscarConfigurationService()->getMasses());

        foreach ($this->getOscarConfigurationService()->getMasses() as $masseKey => $masseLabel) {
            $synthesis[$masseKey] = [
                'label' => $masseLabel,
                'total' => 0.0,
                'total_engage' => 0.0,
                'total_effectue' => 0.0,
                'nbr_engage' => 0,
                'nbr_effectue' => 0,
                'nbr' => 0
            ];
        }


        $numSifacDone = [];
        /** @var SpentLine $spent */
        foreach ($spents as $spent) {
            if( !in_array($spent->getPfi(), $pfis) ){
                $pfis[] = $spent->getPfi();
            }
            if($spent->getBtart() == SpentLine::BTART_EFFECTUE && !in_array($spent->getNumSifac(), $numSifacDone)){
                $numSifacDone[] = $spent->getNumSifac();
            }
        }

        /** @var SpentLine $spent */
        foreach ($spents as $spent) {
            $compte = $this->getCompte($spent->getCompteGeneral());
            $masse = $masseGroup = $compte['annexe'] != '' ? $compte['annexe'] : $compte['masse_inherit'];
            if( !array_key_exists($masse, $synthesis) ){
                $masse = 'N.B';
            }

            $totalEffectue = 0.0;
            $totalEngage = 0.0;

            if( $spent->getBtart() == SpentLine::BTART_ENGAGE ){
                $synthesis[$masse]['nbr_engage']++;
                if( !in_array($spent->getNumSifac(), $numSifacDone) ){
                    $totalEngage = $spent->getMontant();
                }
            }

            if( $spent->getBtart() == SpentLine::BTART_EFFECTUE ){
                $synthesis[$masse]['nbr_effectue']++;
                $totalEffectue = $spent->getMontant();
            }

            $synthesis[$masse]['total'] += $spent->getMontant();
            $synthesis[$masse]['total_effectue'] += $totalEffectue;
            $synthesis[$masse]['total_engage'] += $totalEngage;

            if( in_array($masse, $massesKeys) ){
                $synthesis['totaux']['effectue'] += $totalEffectue;
                $synthesis['totaux']['engage'] += $totalEngage;
            }
            $synthesis[$masse]['nbr']++;

        }
        $synthesis['pfis'] = $pfis;


        return $synthesis;
    }

    public function getSpentDatasSynthesisByPfi($pfi){
        $spents = $this->getSpentsByPFI($pfi);
        return $this->getSpentDatasSynthesisBySpents($spents);
    }

    public function getGroupedSpentsDatas($pfi, $spentType = self::SPENT_EFFECTIVE)
    {
        $re = '/^(0*)([0-9]*)$/m';
        $spents = $this->getSpentsByPFI($pfi, $spentType);
        $out = [];
        $grouped = [];
        $byMasses = [
            'N.B' => [
                'label' => 'Non-définit',
                'key' => 'N.B',
                'spents' => []
            ],
            '1' => [
                'label' => 'Recette',
                'key' => '1',
                'spents' => []
            ],
            '0' => [
                'label' => 'Ignoré',
                'key' => '0',
                'spents' => []
            ]
        ];

        // Tableau contenant les masses
        $massesKey = array_keys($this->getOscarConfigurationService()->getMasses());
        $massesKey[] = 'N.B';
        $massesKey[] = '0';
        $massesKey[] = '1';

        // Rangement des dépenses par masse
        foreach ($this->getOscarConfigurationService()->getMasses() as $masseKey=>$masseLabel) {
            $byMasses[$masseKey] = [
                'label' => $masseLabel,
                'key' => $masseKey,
                'spents' => []
            ];
        }

        /** @var SpentLine $spent */
        foreach ($spents as $spent) {

            $numPiece = $spent->getNumPiece();
            $compteBudg = $spent->getCompteBudgetaire();
            $compte = $this->getCompte($spent->getCompteGeneral());
            $masse = $masseGroup = $compte['annexe'] ? $compte['annexe'] : $compte['masse_inherit'];
            $type = $this->getTypeByCode($spent->getCompteGeneral());

            if( !in_array($masseGroup, $massesKey) ){
                $masseGroup = 'N.B';
            }
            if( !is_array($byMasses[$masseGroup]) ){
                echo "<h1>$masseGroup</h1>";
                var_dump($compte);
                var_dump($byMasses); die();
            }
            if (!array_key_exists($numPiece, $byMasses[$masseGroup]['spents'])) {
                $byMasses[$masseGroup]['spents'][] = [
                    'ids' => [],
                    'numpiece' => $numPiece,
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

        $grouped['byMasses'] = $byMasses;

        return $grouped;
    }

    /**
     * Retourne les dépenses prévisionnelles de l'activité.
     *
     * @param $activity
     * @param bool $justValue
     * @return array
     */
    public function getPrevisionnalSpentsActivity($activity, $justValue = false)
    {
        $out = [];

        $query = $this->getEntityManager()->getRepository(EstimatedSpentLine::class)
            ->createQueryBuilder('e')
            ->where('e.activity = :activity')
            ->getQuery();

        $estimatedSpents = $query->setParameter('activity', $activity)->getResult();

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


    /**
     * FIX : Les dépenses prévisionnelles peuvent être renseignée sur des activités qui n'ont pas encore de PFI
     * (le prévisionnel est d'ailleurs réalisé avant d'obtenir le financement)
     * @deprecated
     */
    public function getPrevisionnalSpentsByPfi($pfi, $justValue = false)
    {
        throw new OscarException("DEPRECATED : getPrevisionnalSpentsByPfi");
    }

    public function getConnector()
    {
        $oscarConfig = $this->getOscarConfigurationService();
        $connectorConfig = $oscarConfig->getConfiguration('connectors.spent');
        $keysConfig = array_keys($connectorConfig);

        if (count($keysConfig) == 0) {
            $this->getLoggerService()->critical("Aucun connector configuré pour les dépenses");
            throw new OscarException("Pas de synchronisation des dépenses configuré");
        } elseif (count($keysConfig) > 1) {
            $this->getLoggerService()->critical("Erreur : Plusieurs connector configuré pour les dépenses");
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
        $spentLine->setRldnr($data['AB9']);
        $spentLine->setBtart($data['BTART']);
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