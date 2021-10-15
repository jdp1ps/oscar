<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 12:01
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;

class TimesheetRepository extends EntityRepository
{


    /**
     * @param $uid
     * @return TimeSheet[]
     */
    public function getTimesheetsByIcsFileUid($uid)
    {
        return $this->createQueryBuilder('t')
            ->where('t.icsFileUid = :uid')
            ->setParameter('uid', $uid)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne le nombre de créneaux créés par le personne
     * @param $personId
     * @return mixed
     */
    public function countTimesheetsForPerson($personId)
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.person = :person');


        return $qb->setParameter('person', $personId)->getQuery()->getSingleScalarResult();
    }

    public function getPeriodsPerson($personId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('a.dateStart', 'a.dateEnd')
            ->from(Activity::class, 'a')
            ->innerJoin('a.workPackages', 'awp')
            ->innerJoin('awp.persons', 'wpp')
            ->where('wpp.person = :personId')
            ->setParameters(['personId' => $personId]);;

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function getTimesheetTotalByPeriodPerson($personId)
    {
        $timesheets = $this->findBy(['person' => $personId]);
        $result = [];

        /** @var TimeSheet $timesheet */
        foreach ($timesheets as $timesheet) {
            $period = $timesheet->getPeriodCode();
            if (!array_key_exists($period, $result)) {
                $result[$period] = 0.0;
            }
            $result[$period] += $timesheet->getDuration();
        }

        return $result;
    }


    /**
     * Retourne la liste des déclarations qui ont un lot de travail.
     * @return array
     */
    public function getTimesheetsWithWorkPackage()
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.workpackage', 'wp')
            ->getQuery()
            ->getResult();
    }

    public function getDatasDeclarerSynthesis($personIds)
    {
        $result = $this->getEntityManager()->getConnection()->fetchAll(
            "SELECT p.id as person_id, CONCAT(p.firstname, ' ', p.lastname) as displayname, to_char(t.datefrom, 'YYYY-MM') as period, t.activity_id, COALESCE(pr.acronym, t.label) as context, CASE WHEN t.activity_id > 0 THEN 'wp' ELSE 'other' END as type, SUM(EXTRACT(EPOCH from dateto - datefrom) / 3600) as duration FROM timesheet t INNER JOIN person p ON p.id = t.person_id LEFT JOIN activity a ON t.activity_id = a.id LEFT JOIN project pr ON pr.id = a.project_id WHERE p.id IN(" . implode(
                ',',
                $personIds
            ) . ") GROUP BY p.id, period, context, activity_id ORDER BY p.lastname, period"
        );
        return $result;
    }

    public function getPersonPeriodSynthesis($personIds, $period)
    {
        if (count($personIds) == 0) {
            throw new \Exception("Aucune personne");
        }

        if (!$period) {
            throw new \Exception("Période manquante");
        }

        $query = "SELECT 
            CONCAT(pr.firstname, ' ', pr.lastname) as person, 
            p.acronym acronym,
            t.person_id,
            t.label,
            COALESCE(w.code, t.label) as itemkey,
            t.activity_id,
            t.workpackage_id,
            EXTRACT(EPOCH from dateto - datefrom) / 3600 as duration,
            to_char(t.datefrom, 'YYYY-MM') as period, 
            t.activity_id
        FROM timesheet t 
        LEFT JOIN workpackage w ON w.id = workpackage_id 
        LEFT JOIN activity a ON a.id = t.activity_id 
        LEFT JOIN project p ON p.id = a.project_id 
        LEFT JOIN person pr ON t.person_id = pr.id
        WHERE 
        
            person_id IN(" . implode(',', $personIds) . ")
            AND
            to_char(t.datefrom, 'YYYY-MM')  = '$period'
            ;";

        $rsm = new ResultSetMapping();
        $result = $this->getEntityManager()->getConnection()->fetchAll($query);

        return $result;
    }
}