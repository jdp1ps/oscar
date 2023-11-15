<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 23/08/18
 * Time: 10:51
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Oscar\Exception\OscarException;
use Oscar\Utils\DateTimeUtils;
use Oscar\Utils\PeriodInfos;

class ValidationPeriodRepository extends EntityRepository
{
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// QUERIES
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getValidationPeriodsValidationProgressQuery(){
        return $this->createQueryBuilder('vp')
            ->where('vp.status IN(:status)')
            ->setParameters([
                'status' => [ValidationPeriod::STATUS_STEP1, ValidationPeriod::STATUS_STEP2, ValidationPeriod::STATUS_STEP3]
            ]);
    }

    public function getValidationPeriodsByActivityIdQuery( $activityId ){
        return $this->createQueryBuilder('vp')
            ->where('vp.object_id = :activityId')
            ->setParameters([
                'activityId' => $activityId
            ]);
    }

    public function getValidationPeriodsPersonQuery( $personId ){
        return $this->createQueryBuilder('vp')
            ->where('vp.declarer = :personId')
            ->setParameters([
                'personId' => $personId
            ]);
    }

    public function getValidationPeriodsPerson( $personId ){
        return $this->getValidationPeriodsPersonQuery($personId)->getQuery()->getResult();
    }

    /**
     * Retourne la liste des toutes les ValidationPeriod pour les personnes données.
     *
     * @param array $personsIds
     * @return int|mixed|string
     */
    public function getValidationPeriodsPersons( array $personsIds, ?int $filterYear = null ){

        $query = $this->createQueryBuilder('vp')
            ->where('vp.declarer IN (:ids)')
            ->setParameters(['ids' => $personsIds])
            ->orderBy('vp.year, vp.month');

        if( $filterYear !== null ){
            $query->andWhere('vp.year = :year')
                ->setParameter('year', $filterYear)
            ;
        }
        return $query->getQuery()->getResult();
    }

    /**
     * Retourne la liste des toutes les ValidationPeriod.
     *
     * @return int|mixed|string
     */
    public function getValidationPeriods( ?int $filterYear = null ){

        $query = $this->createQueryBuilder('vp')
            ->orderBy('vp.year, vp.month');

        if( $filterYear !== null ){
            $query->andWhere('vp.year = :year')
                ->setParameter('year', $filterYear)
            ;
        }
        return $query->getQuery()->getResult();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// GET ENTITIES
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Retourne la liste des validations en cours
     *
     * @return ValidationPeriod[]
     */
    public function getValidationPeriodsValidationProgress(){
        return $this->getValidationPeriodsValidationProgressQuery()->getQuery()->getResult();
    }

    public function getPredictedPeriods( Person $p ){

        $activityBounds = [];

        /** @var WorkPackagePerson $workPackage */
        foreach ($p->getWorkPackages() as $workPackage){
            $key = $workPackage->getWorkPackage()->getActivity()->getOscarNum();
            $activityBounds[$key] = [
                'activity' => $workPackage->getWorkPackage()->getActivity()->toJson(),
                'periods' => $workPackage->getWorkPackage()->getActivity()->getPredictedPeriods(),
                'declarations' => []
            ];
        }

        return $activityBounds;
    }

    public function getPredictedPeriodsPack( Person $person ){
        $activityBounds = [];
        $warnings = [];
        $periods = [];
        $validationsPeriodsSorted = [];

        $validationsPeriods = $this->getValidationPeriodsPersonQuery($person->getId())->getQuery()->getResult();

        /** @var ValidationPeriod $vp */
        foreach ($validationsPeriods as $vp) {
            $month = $vp->getMonth() < 10 ? '0'.$vp->getMonth() : $vp->getMonth();
            $key = $vp->getYear().'-'.$month;
            if( !array_key_exists($key, $periods) ){
                $periods[$key] = [];
            }
            $vpKey = $vp->getObject();

            $periods[$key][] = $vpKey;
        }


        /** @var WorkPackagePerson $workPackage */
        foreach ($person->getWorkPackages() as $workPackage){
            $bounds = $workPackage->getWorkPackage()->getActivity()->getPredictedPeriods();

            if( $bounds['warnings'] ) {
                $warnings[] = $bounds['warnings'];
            }
            else {
                foreach ($bounds['periods'] as $period) {
                    if( !array_key_exists($period, $periods) ){
                        $periods[$period] = [];
                    }
                }
            }
        }

        ksort($periods);

        return $periods;
    }

    /**
     * @param Activity $activity
     * @see ValidationPeriodRepository->getValidationPeriodsByActivityId
     * @return array
     */
    public function getValidationPeriodsByActivity( Activity $activity ){
        return $this->getValidationPeriodsByActivityId($activity->getId());
    }

    /**
     * Retourne la liste des validations pour l'activité.
     *
     * @param $activityId
     * @return array
     */
    public function getValidationPeriodsByActivityId( $activityId ){
        return $this->getValidationPeriodsByActivityIdQuery($activityId)
            ->orderBy('vp.year, vp.month')
            ->getQuery()
            ->getResult();
    }

    public function getValidationPersonStats( Person $person ){
        $validationsPeriodPerson = $this->createQueryBuilder('vp')
            ->where('vp.declarer = :person')
            ->setParameters(['person' => $person]);
        $out = [
            "wait" => 0,
            "conflict" => 0,
            "valid" => 0
        ];

        /** @var ValidationPeriod $validationPeriod */
        foreach ( $validationsPeriodPerson as $validationPeriod ){
            if( $validationPeriod->getStatus() == ValidationPeriod::STATUS_CONFLICT ){
                $out['conflict']++;
            }
            elseif( $validationPeriod->getStatus() == ValidationPeriod::STATUS_VALID ){
                $out['valid']++;
            }
            else {
                $out['wait']++;
            }
        }
        return $out;
    }

    /**
     * @param int $declarer_id
     * @param int $year
     * @param int $month
     * @return \Doctrine\ORM\Query
     */
    public function getValidationsDeclarerPeriod( int $declarer_id, int $year, int $month )
    {
        return $this->createQueryBuilder('vp')
            ->select('vp')
            ->leftJoin('vp.validatorsPrj', 'vprj')
            ->leftJoin('vp.validatorsSci', 'vsci')
            ->leftJoin('vp.validatorsAdm', 'vadm')
            ->where("vp.declarer = :declarer_id 
                AND vp.month = :month 
                AND vp.year = :year"
            )
            /* //->setParameter('person', $validator_id) */
            ->setParameter('declarer_id', $declarer_id)
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->getQuery();
    }

    public function getValidationsPeriodPerson( $personId ){
       return $this->createQueryBuilder('vp')
            ->where('vp.declarer = :person')
            ->setParameters(['person' => $personId])
           ->getQuery()
           ->getResult();
    }

    /**
     * @param $status
     * @return string
     */
    public static function getStatusText( $status ) :string
    {
        return self::getStatusTexts()[$status];
    }

    /**
     * @return array
     */
    public static function getStatusTexts() :array
    {
        static $STATUS_TEXTS;
        if( $STATUS_TEXTS === null ){
            $STATUS_TEXTS = [
                'send-prj' => 'Validation projet',
                'send-sci' => 'Validation scientifique',
                'send-adm' => 'Validation administrative',
                'conflict' => 'Conflit à résoudre',
                'valid' => 'Validé',
            ];
        }
        return $STATUS_TEXTS;
    }

    /**
     * @param int $validatorId Identifiant Person
     * @return array
     */
    public function getValidationPeriodForValidator( int $validatorId ):array
    {
        $query = $this->createQueryBuilder('vp')
            ->select('vp')
            ->innerJoin('vp.declarer', 'declarer')
            ->leftJoin('vp.validatorsPrj', 'vprj')
            ->leftJoin('vp.validatorsSci', 'vsci')
            ->leftJoin('vp.validatorsAdm', 'vadm')
            ->where("vprj = :person OR vsci = :person OR vadm = :person")
            ->setParameter('person', $validatorId)
            ->getQuery();

        $out = [];
        /** @var ValidationPeriod $validationPeriod */
        foreach ($query->getResult() as $validationPeriod) {
            $validable = false;

            // Activity
            $activity_id = null;
            $activity_acronym = "";
            if( $validationPeriod->isActivityValidation() ){
                $activity_id = $validationPeriod->getObjectId();
                $activity_acronym = "MISSING ACRONYM";
                try {
                    $activity = $this->getEntityManager()->getRepository(Activity::class)->find($activity_id);
                    $activity_acronym = $activity->getAcronym();
                } catch (\Exception $e) {

                }
            }

            $validators = [];

            /** @var Person $validator */
            foreach ($validationPeriod->getCurrentValidators() as $validator){
                $validator_fullname = $validator->getFullName();
                if( !in_array($validator_fullname, $validators) ){
                    array_push($validators, $validator_fullname);
                }
                if( $validator->getId() == $validatorId ){
                    $validable = true;
                }
            }

            $out[] = [
                'id' => $validationPeriod->getId(),
                'declarer_id' => $validationPeriod->getDeclarer()->getId(),
                'declarer_fullname' => (string) $validationPeriod->getDeclarer(),
                'declarer_affectation' => $validationPeriod->getDeclarer()->getLdapAffectation(),
                'validators' => $validators,
                'period' => $validationPeriod->getPeriod(),
                'statut' => $validationPeriod->getStatus(),
                'statut_test' => self::getStatusText($validationPeriod->getStatus()),
                'validable' => $validable,
                'activity_id' => $activity_id,
                'activity_acronym' => $activity_acronym
            ];
        }

        return $out;
    }


    /**
     * Retourne la liste des validations disponible pour la personne et la période donnée.
     *
     * @param $personId
     * @param $periodStr
     * @return int|mixed|string
     */
    public function getValidationPeriodForPersonAtPeriod(int $personId, string $periodStr){
        // Récupération des données de la périodes
        $periodInfos = PeriodInfos::getPeriodInfosObj($periodStr);

        return $this->createQueryBuilder('vp')
            ->where('vp.declarer = :person AND vp.year = :year AND vp.month = :month')
            ->setParameters([
                'person' => $personId,
                'year' => $periodInfos->getYear(),
                'month' => $periodInfos->getMonth(),
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $personIds
     * @param string $periodStr
     * @return \Doctrine\ORM\QueryBuilder
     * @throws OscarException
     */
    public function getValidationPeriodsForPersonsAtPeriod( array $personIds, string $periodStr )
    {
        $periodInfos = PeriodInfos::getPeriodInfosObj($periodStr);

        return $this->createQueryBuilder('v')
            ->where('v.declarer IN(:personIds) AND v.month = :month AND v.year = :year')
            ->setParameters(
                [
                    'personIds' => $personIds,
                    'year' => $periodInfos->getYear(),
                    'month' => $periodInfos->getMonth(),
                ]
            )->getQuery()->getResult();
    }
    /**
     * @param array $personIds
     * @param string $periodStr
     * @return \Doctrine\ORM\QueryBuilder
     * @throws OscarException
     */
    public function getValidationPeriodsForPersonsAtPeriodBounds( array $personIds, string $from, string $to)
    {
        $start = PeriodInfos::getPeriodInfosObj($from);
        $end = PeriodInfos::getPeriodInfosObj($to);

        $query = $this->createQueryBuilder('v')
            ->select('v')
            ->where("v.declarer IN(:personIds) 
                AND CONCAT(v.year, '-', v.month) >= :speriod 
                AND CONCAT(v.year, '-', v.month) <= :fperiod 
                " )
            ->setParameters(
                [
                    'personIds' => $personIds,
                    'speriod' => $start->getPeriodSimple(),
                    'fperiod' => $end->getPeriodSimple()
                ]
            )->getQuery();

        return $query->getResult();
    }

    /**
     * Retourne LA procédure de validation pour : La période, l'activité et la personne.
     *
     * @param $year
     * @param $month
     * @param $activityId
     * @param $personId
     * @return null|ValidationPeriod
     * @throws OscarException
     */
    public function getValidationPeriodForActivity( $year, $month, $activityId, $personId ){
        $query = $this->createQueryBuilder('vp')
            ->where('vp.month = :month AND vp.year = :year AND vp.object_id = :activityId AND vp.declarer = :personId');

        $result = $query->setParameters([
            'month' => $month,
            'year' => $year,
            'personId' => $personId,
            'activityId' => $activityId,
        ])->getQuery()->getResult();

        if( count($result) == 1 ){
            return $result[0];
        }
        elseif ( count($result) > 1 ){
            throw new OscarException("ERREUR FATALE : plusieurs procédure de validation ont été trouvée pour cette activité ('$activityId') à la même période...");
        }
        else {
            return null;
        }
    }

    /**
     * Retourne la procédure de validation en cours pour l'élément hors-lot
     *
     * @param $year
     * @param $month
     * @param $code
     * @param $personId
     * @return null|ValidationPeriod
     * @throws OscarException
     */
    public function getValidationPeriodOutWP( $year, $month, $code, $personId ){
        $query = $this->createQueryBuilder('vp')
            ->where('vp.month = :month AND vp.year = :year AND vp.object = :code AND vp.declarer = :personId');

        $result = $query->setParameters([
            'month' => $month,
            'year' => $year,
            'personId' => $personId,
            'code' => $code,
        ])->getQuery()->getResult();

        if( count($result) == 1 ){
            return $result[0];
        }
        elseif ( count($result) > 1 ){
            throw new OscarException("ERREUR FATALE : plusieurs procédure de validation ont été trouvée pour ce type de déclaration ('$code') ...");
        }
        else {
            return null;
        }
    }

    public function getValidationPeriodPersonWithConflict( $personId ){
        $status = ValidationPeriod::STATUS_CONFLICT;
        $query = $this->getValidationPeriodsPersonQuery($personId)
            ->andWhere("vp.status = '$status'");
        return $query->getQuery()->getResult();
    }

    /**
     * Retourne la liste des validations Hors-Lots à valider.
     *
     * @param int|null $idPerson
     * @return ValidationPeriod[]
     */
    public function getValidationPeriodsOutWPToValidate( ?int $idPerson = null ) :array
    {
        $parameters = [
            'objectgroup' => ValidationPeriod::GROUP_OTHER,
            'status' => [ValidationPeriod::STATUS_STEP1, ValidationPeriod::STATUS_STEP2, ValidationPeriod::STATUS_STEP3],
        ];

        $query = $this->createQueryBuilder('vp')
            ->where('vp.objectGroup = :objectgroup')
            ->andWhere('vp.status IN(:status)');

        if( $idPerson != null ){
            $parameters['idPerson'] = $idPerson;
            $query->andWhere('vp.declarer = :idPerson');
        }
        return $query->setParameters($parameters)->getQuery()->getResult();
    }

    public function getValidationPeriodsOutWP( $idPerson = null ){
        $parameters = [
            'objectgroup' => ValidationPeriod::GROUP_OTHER
        ];

        $query = $this->createQueryBuilder('vp')
            ->where('vp.objectGroup = :objectgroup');

        if( $idPerson != null ){
            $parameters['idPerson'] = $idPerson;
            $query->andWhere('vp.declarer = :idPerson');
        }

        $query->addOrderBy('vp.year', 'DESC');
        $query->addOrderBy('vp.month', 'DESC');
        return $query->setParameters($parameters)->getQuery()->getResult();
    }

    public function getDatasValidationPersonsPeriod($personsIds, $start, $end){

        try {
            $stm = $this->getEntityManager()->getConnection()->prepare("SELECT CONCAT(v.year, '-', v.month) as period, * 
        FROM validationperiod as v WHERE v.declarer_id IN(".implode(',', $personsIds).") AND v.year >= $start AND v.year <= $end");



            $result = $stm->executeQuery();
            return $result->fetchAllAssociative();
        } catch (\Exception $e) {
            throw new OscarException("ERREUR SQL : Impossible de charger des données de validation");
        }

        /*
        $result = $this->getEntityManager()->getConnection()->fetchAll("SELECT CONCAT(v.year, '-', v.month) as period, *
	FROM validationperiod as v WHERE v.declarer_id IN(".implode(',', $personsIds).") AND v.year >= $start AND v.year <= $end");
        */

        /*
    SELECT p.id as person_id, CONCAT(p.firstname, ' ', p.lastname) as displayname, to_char(t.datefrom, 'YYYY-MM') as period, t.activity_id, COALESCE(pr.acronym, t.label) as context, CASE WHEN t.activity_id > 0 THEN 'wp' ELSE 'other' END as type, SUM(EXTRACT(EPOCH from dateto - datefrom) / 3600) as duration FROM timesheet t INNER JOIN person p ON p.id = t.person_id LEFT JOIN activity a ON t.activity_id = a.id LEFT JOIN project pr ON pr.id = a.project_id WHERE p.id IN(".implode(',', $personIds).") GROUP BY p.id, period, context, activity_id ORDER BY p.lastname, period
         */

    }




    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// GET DATAS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Retourne la liste des IDS des activités avec une validation en cours.
     *
     * @return integer[]
     */
    public function getValidationPeriodsValidationProgressActivitiesIds(){
        $datas = $this->getValidationPeriodsValidationProgressQuery()
            ->andWhere("vp.object_id != '-1'")
            ->select('vp.object_id')->getQuery()->getResult();
        return $ids = array_map('current', $datas);
    }
}
