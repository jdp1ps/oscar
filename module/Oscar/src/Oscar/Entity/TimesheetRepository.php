<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 12:01
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class TimesheetRepository extends EntityRepository {


    /**
     * @param $uid
     * @return TimeSheet[]
     */
    public function getTimesheetsByIcsFileUid( $uid ){
        return $this->createQueryBuilder('t')
            ->where('t.icsFileUid = :uid')
            ->setParameter('uid', $uid)
            ->getQuery()
            ->getResult();
    }
}