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
    protected function getBaseQueryAdministration( $status ){

        $qb = $this->createQueryBuilder('ar')
            ->where('ar.status IN (:status)')
            ->setParameter('status', $status);

        return$qb;
    }

    /**
     * @return mixed
     */
    public function getAll( $status ){
        if( count($status) == 0 ) return [];
        return $this->getBaseQueryAdministration($status)->getQuery()->getResult();
    }

    /**
     * @param $organizations
     * @return mixed
     */
    public function getAllForOrganizations( $organizations, $status ){

        if( count($status) == 0 ) return [];

        $qb = $this->getBaseQueryAdministration($status);

        $qb->andWhere('ar.organisation IN(:organizations)')
            ->setParameter('organizations', $organizations);

        return $qb->getQuery()->getResult();
    }

    public function getAllForPerson( Person $person, $status = null ){

        $mode = [ActivityRequest::STATUS_DRAFT, ActivityRequest::STATUS_SEND];
        if( $status != null ){
            $mode = $status;
        }
        $qb = $this->getBaseQueryAdministration($mode);

        $qb->andWhere('ar.createdBy = :person')
            ->setParameter('person', $person);

        return $qb->getQuery()->getResult();

    }
}