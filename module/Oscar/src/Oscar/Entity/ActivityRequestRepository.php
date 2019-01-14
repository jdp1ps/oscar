<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/01/19
 * Time: 15:51
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;

class ActivityRequestRepository extends EntityRepository
{

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getBaseQueryAdministration( $mode = "active" ){

        if( $mode == "active" ){
            $status = [ActivityRequest::STATUS_DRAFT];
        }
        else if( $mode == 'history' ){
            $status = [ActivityRequest::STATUS_VALID, ActivityRequest::STATUS_REJECT];
        }
        else if (is_array($mode)) {
            $status = $mode;
        }

        $qb = $this->createQueryBuilder('ar')
            ->where('ar.status IN (:status)')
            ->setParameter('status', $status);

        return$qb;
    }

    /**
     * @return mixed
     */
    public function getAll( $history = false ){
        $mode = 'active';
        if( $history !== false ){
            $mode = 'history';
        }
        return $this->getBaseQueryAdministration($mode)->getQuery()->getResult();
    }

    /**
     * @param $organizations
     * @return mixed
     */
    public function getAllForOrganizations( $organizations, $history = false ){
        $mode = 'active';
        if( $history !== false ){
            $mode = 'history';
        }
        $qb = $this->getBaseQueryAdministration($mode);

        $qb->andWhere('ar.organisation IN(:organizations)')
            ->setParameter('organizations', $organizations);

        return $qb->getQuery()->getResult();
    }

    public function getAllForPerson( Person $person, $history = false ){

        $mode = [ActivityRequest::STATUS_DRAFT, ActivityRequest::STATUS_SEND];
        if( $history !== false ){
            $mode = 'history';
        }
        $qb = $this->getBaseQueryAdministration($mode);

        $qb->andWhere('ar.createdBy = :person')
            ->setParameter('person', $person);

        return $qb->getQuery()->getResult();

    }
}