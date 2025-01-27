<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 12:01
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class ActivityAvenantRepository extends EntityRepository
{
    public function getByActivityId(int $activityId):array {
        return $this->createQueryBuilder('a')
            ->where('a.activity = :activityId')
            ->setParameter('activityId', $activityId)
            ->getQuery()
            ->getResult();
    }
}
