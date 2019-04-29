<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 12:01
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
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


    /**
     * Retourne la liste des déclarations qui ont un lot de travail.
     * @return array
     */
    public function getTimesheetsWithWorkPackage(){
        return $this->createQueryBuilder('t')
            ->innerJoin('t.workpackage', 'wp')
            ->getQuery()
            ->getResult();
    }

    public function getDatasDeclarerSynthesis($personIds){
        $rsm = new ResultSetMapping();
        $result = $this->getEntityManager()->getConnection()->fetchAll("SELECT p.id as person_id, CONCAT(p.firstname, ' ', p.lastname) as displayname, to_char(t.datefrom, 'YYYY-MM') as period, t.activity_id, COALESCE(pr.acronym, t.label) as context, CASE WHEN t.activity_id > 0 THEN 'wp' ELSE 'other' END as type, SUM(EXTRACT(EPOCH from dateto - datefrom) / 3600) as duration FROM timesheet t INNER JOIN person p ON p.id = t.person_id LEFT JOIN activity a ON t.activity_id = a.id LEFT JOIN project pr ON pr.id = a.project_id WHERE p.id IN(".implode(',', $personIds).") GROUP BY p.id, period, context, activity_id ORDER BY p.lastname, period");

        return $result;
    }
}