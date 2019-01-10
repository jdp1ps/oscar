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
    protected function getBaseQueryAdministration(){
        $qb = $this->createQueryBuilder('ar')
            ->where('ar.status != :status')
            ->setParameter('status', ActivityRequest::STATUS_DRAFT);

        return$qb;
    }

    /**
     * @return mixed
     */
    public function getAll(){
        return $this->getBaseQueryAdministration()->getQuery()->getResult();
    }

    /**
     * @param $organizations
     * @return mixed
     */
    public function getAllForOrganizations( $organizations ){
        $qb = $this->createQueryBuilder('ar')
            ->where('ar.organization IN(:organizations)')
            ->setParameter('organizations', $organizations);
        return $qb->getQuery()->getResult();
    }
}