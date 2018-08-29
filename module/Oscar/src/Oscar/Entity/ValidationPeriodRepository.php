<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 23/08/18
 * Time: 10:51
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Oscar\Exception\OscarException;

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

    public function getValidationPeriodsOutWPToValidate( $idPerson = null ){
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
