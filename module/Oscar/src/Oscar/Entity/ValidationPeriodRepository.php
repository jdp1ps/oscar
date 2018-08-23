<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 23/08/18
 * Time: 10:51
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;

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
