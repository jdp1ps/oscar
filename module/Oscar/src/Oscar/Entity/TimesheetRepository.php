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
use Oscar\Utils\PeriodInfos;

class TimesheetRepository extends EntityRepository
{

    protected function getQueryBuilderBase()
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.activity, t.dateFrom');
    }

    public function getActivitiesIdsForDeclarer( int $declarerId, ?string $period = "" )
    {
        try {
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('activity_id', 'activity_id');

            $params = [
                'declarerId' => $declarerId
            ];

            $sql = 'select distinct activity_id from workpackage wp 
                        inner join workpackageperson wpp on wpp.workpackage_id = wp.id 
                        inner join activity a on wp.activity_id = a.id 
                        where wpp.person_id = :declarerId';

            if( $period ){
                $sql .= ' and a.dateStart <= :period_end and a.dateEnd >= :period_start';
                $p = PeriodInfos::getPeriodInfosObj($period);
                $params['period_start'] = $p->getStart()->format('Y-m-d');
                $params['period_end'] = $p->getEnd()->format('Y-m-d');
            }

            $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
            $results = $query->setParameters($params)->getResult();

            return array_map('current', $results);

        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Retourne la liste des créneaux de la personne (ID).
     *
     * @param int $personId
     * @param bool $validatedOnly
     * @param int|null $activityId
     * @return int|mixed|string
     */
    public function getForPerson( int $personId, bool $validatedOnly = false, ?int $activityId = null )
    {
        $qb = $this->getEntityManager()->getRepository(TimeSheet::class)
            ->createQueryBuilder('t')
            ->where('t.person = :person')
            ->orderBy('t.activity, t.dateFrom')
            ->setParameter('person', $personId);

        if ($activityId != null) {
            $qb->andWhere('t.activity = :activity')
                ->setParameter('activity', $activityId);
        }

        return $qb->getQuery()->getResult();
    }


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
     * @param int $personId
     * @return int|mixed|string
     */
    public function getTimesheetsPerson(int $personId)
    {
          return $this->createQueryBuilder('t')
              ->where('t.person = :person')
              ->setParameter('person', $personId)
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

    /**
     * @param array $uids
     * @return TimeSheet[]
     */
    public function getImportedByUid( array $uids ) :array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t')
            ->where('t.icsUid IN(:uids)');

        return $qb->setParameter('uids', $uids)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne la liste des périodes de déclaration de la personne.
     *
     * @param int $personId
     * @param bool $incudeNonActive
     * @return int|mixed|string
     */
    public function getPeriodsPerson(int $personId, bool $incudeNonActive = false )
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('a.dateStart', 'a.dateEnd', 'a.id', 'a.label', 'p.acronym')
            ->from(Activity::class, 'a')
            ->innerJoin('a.workPackages', 'awp')
            ->innerJoin('a.project', 'p')
            ->innerJoin('awp.persons', 'wpp')
            ->where('wpp.person = :personId');

        $parameters = [
            'personId' => $personId
        ];

        if( $incudeNonActive == false ){
            $qb->andWhere('a.status = :status');
            $parameters['status'] = Activity::STATUS_ACTIVE;
        }

        return $qb->setParameters($parameters)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
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

    /**
     * Retourne la synthèse des déclarations de temps pour les personnes int[] (liste des ID des personnes)
     * pour les périodes string[] (tableaux de chaînes sous la forme YYYY-MM).
     *
     * @param $personIds
     * @param null $periods
     * @return mixed[]
     */
    public function getDatasDeclarerSynthesis($personIds, $periods = null)
    {
        $sql = "SELECT p.id as person_id, 
                    CONCAT(p.firstname, ' ', p.lastname) as displayname, 
                    to_char(t.datefrom, 'YYYY-MM') as period, t.activity_id, 
                    COALESCE(pr.acronym, t.label) as context, 
                    CASE WHEN t.activity_id > 0 THEN 'wp' ELSE 'other' END as type, 
                    SUM(EXTRACT(EPOCH from dateto - datefrom) / 3600) as duration 
                FROM timesheet t 
                INNER JOIN person p ON p.id = t.person_id 
                LEFT JOIN activity a ON t.activity_id = a.id 
                LEFT JOIN project pr ON pr.id = a.project_id 
                WHERE p.id IN(".implode(',', $personIds).")";

        if( $periods ){
            $sql .= "AND to_char(t.datefrom, 'YYYY-MM') IN('" . implode("','", $periods) . "') ";
        }
        $sql .= "GROUP BY p.id, period, context, activity_id ORDER BY p.lastname, period";

        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }

    public function getPersonPeriodSynthesisBounds($personIds, $from, $to)
    {
        if (count($personIds) == 0) {
            throw new \Exception("Aucune personne");
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
            to_char(t.datefrom, 'YYYY-MM')  >= '$from' 
            AND
            to_char(t.datefrom, 'YYYY-MM')  <= '$to'
            ;";

        $rsm = new ResultSetMapping();
        $result = $this->getEntityManager()->getConnection()->fetchAll($query);

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