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
    public function getAll(){
        return $this->findAll();
    }

    public function getAllForOrganizations( $organizations ){
        $qb = $this->createQueryBuilder('ar')
            ->where('ar.organization IN(:organizations)')
            ->setParameter('organizations', $organizations);
        return $qb->getQuery()->getResult();
    }
}