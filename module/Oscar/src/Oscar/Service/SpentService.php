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
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Log\Logger;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class SpentService implements UseLoggerService, UseOscarConfigurationService, UseEntityManager
{
    use UseEntityManagerTrait, UseOscarConfigurationServiceTrait, UseLoggerServiceTrait;


    public function getAllArray(){
        $array = [];
        /** @var SpentTypeGroup $spendTypeGroup */
        foreach ($this->getSpentTypeRepository()->getAll() as $spendTypeGroup) {
            $array[] = $spendTypeGroup->toJson();
        }
        return $array;
    }

    public function getSpentTypeById($id){
        return $this->getSpentTypeRepository()->find($id);
    }


    public function orderSpentsByCode(){
        $spents = $this->getSpentTypesIndexCode();
        $bound = 1;
        $open = [];
        foreach ($spents as $code=>$spent) {
            $openIndex = count($open)-1;
            if( $openIndex >= 0 ){
                /** @var SpentTypeGroup $lastOpen */
                $lastOpen = &$open[$openIndex];
                while (strlen($lastOpen->getCode()) >= strlen($spent->getCode())) {
                    $lastOpen->setRgt($bound++);
                    array_pop($open);
                    $openIndex--;
                    if( $openIndex < 0 ){
                        break;
                    }
                    $lastOpen = $open[$openIndex];

                }
            }
            $spent->setLft($bound++);
            $open[] = $spent;

        }

        while (count($open) > 0) {
            $open[count($open)-1]->setRgt($bound++);
            array_pop($open);
        }

        foreach ($spents as $code=>&$spent) {
            echo $spent."<br>";
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @return SpentTypeGroup[]
     */
    public function getSpentTypesIndexCode(){
        $spents = [];
        /** @var SpentTypeGroup $spent */
        foreach ($this->getSpentTypeRepository()->findBy([],['code' => 'ASC']) as $spent) {
            $spents[$spent->getCode()] = $spent;
        }
        return $spents;
    }

    public function createSpentTypeGroup( $datas ){
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
        if( $inside == 'root' ){
            // todo : Récupération du dernier noeud

            $last = $this->getSpentTypeRepository()->getLastSpentTypeGroup();

            if( $last ){
                $lgt = $last->getRgt()+1;
                $rgt = $lgt+1;
            } else {
                $lgt = 1;
                $rgt = $lgt+1;
            }

            $type->setLft($lgt)->setRgt($rgt);
            $this->getEntityManager()->persist($type);
            $this->getEntityManager()->flush($type);
            return $type;
        }
        else {
            $insiderId = intval($inside);
            if ($insiderId < 1 ) throw new OscarException(_("DATA ERROR : Type de destination incohérent"));

            // Récupération du noeud racine
            /** @var SpentTypeGroup $insider */
            $insider = $this->getSpentTypeRepository()->find($insiderId);

            if (!$insider ) throw new OscarException(_("Impossible de localiser l'emplacement pour le nouveau type"));

            $lgt = $insider->getRgt();
            $rgt = $lgt+1;

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

    public function getSpentGroupNodeData( $userInput ){
        $instance = null;

        if( is_object($userInput) ){
            if( $userInput instanceof SpentTypeGroup ){
                $instance = $userInput;
            } else {
                throw new OscarException("Mauvaise entrée utilisateur, impossible de traiter ce type de donnée");
            }
        }

        if( $userInput == "root" ){
            return [
                'id' => 'root',
                'label' => 'root',
                'description' => 'root',
                'lft' => 0,
                'rgt' => $this->getSpentTypeRepository()->count()*2
            ];
        }

        $spentGroup = $this->getSpentTypeRepository()->find($userInput);
        if( $spentGroup ){
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

    public function deleteNode( $spent, $deleteEntity = true )
    {
        $lft = $spent['lft'];
        $rgt = $spent['rgt'];
        $decalage = $rgt - $lft + 1;

        if( $deleteEntity === true ) {
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

    public function moved( $movedId, $destination ){

        /** @var SpentTypeGroup $move */
        $move = $this->getSpentTypeRepository()->find($movedId);

        $sizeBranch = $move->getRgt() - $move->getLft() +1;

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
        $deplacement = $sizeBranch + $destPoint -1;
        $this->getEntityManager()->createNativeQuery(
            'UPDATE spenttypegroup SET lft = lft + :size, rgt = rgt + :size WHERE rgt <= :pos', new ResultSetMapping()
        )->execute(['size' => $deplacement, 'pos' => 0 ]);
    }


    public function getYearsListActivity( Activity $activity ){

        if( !$activity->getDateStart() )
            throw new OscarException(sprintf(_("L'activité %s n'a pas de date de début"), $activity));

        if( !$activity->getDateEnd() )
            throw new OscarException(sprintf(_("L'activité %s n'a pas de date de fin"), $activity));

        if( $activity->getDateEnd() < $activity->getDateStart() ){
            throw new OscarException(sprintf(_("L'activité %s a une date de fin antérieur à sa date de début"), $activity));
        }

        $yearStart  = (int) $activity->getDateStart()->format('Y');
        $yearEnd    = (int) $activity->getDateEnd()->format('Y');

        $years = [];
        for( $i = $yearStart; $i <= $yearEnd; $i++ ){
            $years[] = $i;
        }

        return $years;
    }

    /**
     * Retourne la liste des lignes de budget regroupées par masse
     * @return array
     */
    public function getLinesByMasse(){
        $query = $this->getSpentTypeRepository()->createQueryBuilder('s')
            ->orderBy('s.annexe', 'ASC')
            ->addOrderBy('s.code', 'ASC')
            ->where("s.annexe != ''");

        $result = $query->getQuery()->getArrayResult();
        return $result;
    }

    public function getMasses(){
        return $this->getOscarConfigurationService()->getConfiguration('spenttypeannexes');
    }

    public function getTypesTree(){

        $types = $this->getSpentTypeRepository()->getAll();
        $root = [
            'label' => 'root',
            'lft' => 0,
            // 'corpus' => '',
            'rgt' => count($types)*2 + 1,
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

            $lastParent = &$parents[count($parents)-1];

            while ($r > $lastParent['rgt']) {
                $child = array_pop($parents);
               // $parents[count($parents)-1]['corpus'] .= $child['corpus'];
                $parents[count($parents)-1]['children'][] = $child;
                if( $child['empty'] == false ){
                    $parents[count($parents)-1]['children'][] = false;
                }
                $lastParent = &$parents[count($parents)-1];
            }

            if( $r > $l+1 ){
                $item['children'] = [];
                $parents[] = $item;
            } else {
                $item['parent_id'] = $lastParent['id'];
                $lastParent['children'][] = $item;
               // $lastParent['corpus'] .= $item['corpus'] . ' ';
                if( $item['empty'] == false ){
                    //die($item['code']);
                    foreach ($parents as &$p ){
                        $p['empty'] = false;
                    }
                }

            }
        }

        while (count($parents) > 1) {
            $child = array_pop($parents);
           // $parents[count($parents) - 1]['corpus'] .= $child['corpus'];
            $parents[count($parents) - 1]['children'][] = $child;
            if( $child['empty'] == false ){
                $parents[count($parents)-1]['children'][] = false;
            }
        }

        return $parents[0];
    }

    public function loadPCG(){
        $filepath = $this->getOscarConfigurationService()->getConfiguration('spenttypesource');

        $spentTypes = $this->getSpentTypesIndexCode() ;

        $re = '/(\d+)\.?/';

        if (($handle = fopen($filepath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                if( preg_match($re, $data[0], $matches) ){
                    $code = $matches[1];
                    $label = $data[1];
                }

                else if (preg_match($re, $data[1], $matches) ){
                    $code = $matches[1];
                    $label = $data[2];
                }

                else if (preg_match($re, $data[2], $matches) ){
                    $code = $matches[1];
                    $label = $data[3];
                }

                else if (preg_match($re, $data[3], $matches) ){
                    $code = $matches[1];
                    $label = $data[4];
                }

                else {
                    continue;
                }

                if( array_key_exists($code, $spentTypes) ){

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


    public function admin(){

        $i = 0;
        /** @var SpentTypeGroup $node */
        foreach ($this->getSpentTypeRepository()->findAll() as $node){
            $node->setLft(++$i)->setRgt(++$i);
        }
        $this->getEntityManager()->flush();

    }

    public function updateSpentTypeGroup( $datas ){
        $this->checkDatas($datas);

        $id = intval($datas['id']);

        if( $id < 1 ){
            throw new OscarException(_("Impossible de trouver le type de dépense à mettre à jour"));
        }

        /** @var SpentTypeGroup $spentTypeGroup */
        $spentTypeGroup = $this->getSpentTypeRepository()->find($id);

        if( !$spentTypeGroup ){
            throw new OscarException(_("Le type de dépense n'a pas été trouvé"));
        }

        $label          = $datas['label'];
        $code           = $datas['code'];
        $annexe         = $datas['annexe'];
        $description    = $datas['description'];

        if( array_key_exists('inside', $datas) ){
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
    protected function getSpentTypeRepository(){
        return $this->getEntityManager()->getRepository(SpentTypeGroup::class);
    }

    /**
     * @return Logger
     */
    protected function getLogger(){
        return $this->getLoggerService();
    }

    /**
     * Vérification des données reçues depuis la saisie utilisateur.
     *
     * @param $datas
     * @throws OscarException
     */
    protected function checkDatas( $datas ){
        if( !$datas['label'] ){
            throw new OscarException(_("Vous devez renseigner un intitulé."));
        }
        if( !$datas['code'] ){
            throw new OscarException(_("Le champ code doit être renseigné"));
        }
    }
    public function getSpentsByPFI( $pfi ){
        return $this->getEntityManager()->getRepository(SpentLine::class)->findBy(['pfi' => $pfi], ['datePaiement' => 'ASC']);
    }

    public function syncSpentsByEOTP( $eotp ){
        if( !$eotp ){
            throw new OscarException("Pas d'EOTP");
        }
        return $this->getConnector()->sync($eotp);
    }

    public function getGroupedSpentsDatas($pfi){
        $spents = $this->getSpentsByPFI($pfi);
        $out = [];
        $grouped = [];
        /** @var SpentLine $spent */
        foreach ($spents as $spent) {
            $numPiece = $spent->getNumPiece();
            $compteBudg = $spent->getCompteBudgetaire();

            if( !array_key_exists($numPiece, $grouped) ){
                $grouped[$numPiece] = [
                    'ids' => [],
                    'syncIds' => [],
                    'text' => [],
                    'montant' => 0.0,
                    'compteBudgetaire' => [],
                    'datecomptable' => $spent->getDateComptable(),
                    'datepaiement' => $spent->getDatePaiement(),
                    'annee' => $spent->getDateAnneeExercice(),
                    'refPiece' => $spent->getPieceRef(),
                    'details' => []
                ];
            }



            if( $compteBudg == 'PG_REM' ){
                $grouped[$numPiece]['refPiece'] = $spent->getPieceRef();
            }

            if( $spent->getDesignation() && !in_array($spent->getDesignation(), $grouped[$numPiece]['text']) ){
                $grouped[$numPiece]['text'][] = $spent->getDesignation();
            }
            if( $spent->getTexteFacture() && !in_array($spent->getTexteFacture(), $grouped[$numPiece]['text']) ){
                $grouped[$numPiece]['text'][] = $spent->getTexteFacture();
            }
            if( $spent->getCompteBudgetaire() && !in_array($spent->getCompteBudgetaire(), $grouped[$numPiece]['compteBudgetaire']) ){
                $grouped[$numPiece]['compteBudgetaire'][] = $spent->getCompteBudgetaire();
            }

            $grouped[$numPiece]['ids'][] = $spent->getId();
            $grouped[$numPiece]['syncIds'][] = $spent->getSyncId();
            $grouped[$numPiece]['montant'] += $spent->getMontant();
            $grouped[$numPiece]['details'][] = $spent;
        }

        return $grouped;
    }

    public function getConnector(){
        $oscarConfig = $this->getOscarConfigurationService();
        $connectorConfig = $oscarConfig->getConfiguration('connectors.spent');
        $keysConfig = array_keys($connectorConfig);

        if( count($keysConfig) == 0 ){
            throw new OscarException("Pas de synchronisation des dépenses configuré");
        }

        elseif (count($keysConfig) > 1) {
            throw new OscarException("Oscar ne prends en charge qu'une source de synchronisation pour les dépenses.");
        }
        else {

            $conf = $connectorConfig[$keysConfig[0]];
            $class = $conf['class'];
            $factory = new \ReflectionClass($class);

            /** @var ConnectorSpentSifacOCI $instance */
            $instance = $factory->newInstanceArgs([$this, $conf['params']]);
            return $instance;
        }
    }


    public function addSpentLine( array $data ){
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