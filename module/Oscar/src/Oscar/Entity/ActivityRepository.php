<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 28/08/18
 * Time: 09:15
 */

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;
use Oscar\Exception\OscarException;
use Oscar\Utils\DateTimeUtils;

/**
 * Class ActivityRepository
 * @package Oscar\Entity
 */
class ActivityRepository extends EntityRepository
{

    /**
     * @param null $limitEnd
     * @param bool $statusActive
     * @return Activity[]
     */
    public function getActivitiesActive($limitEnd = null, $statusActive = true)
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->where('a.status = :status');

        $queryBuilder->setParameters(
            [
                'status' => Activity::STATUS_ACTIVE
            ]
        );

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Retourne la liste des IDS des activités impliquant l'organisation (avec un rôle principal).
     *
     * @param $idOrganization
     */
    public function getActivitiesIdsForOrganizations($idsOrganization, $principal = true)
    {
        $query = $this->createQueryBuilder('a')
            ->select('a.id')
            ->innerJoin('a.organizations', 'oa')
            ->innerJoin('oa.roleObj', 'oar')
            ->where('oa.organization IN(:idsOrganization) AND oar.principal = :principal');

        $parameters = [
            'idsOrganization' => $idsOrganization,
            'principal' => $principal
        ];

        return array_map(
            'current',
            $query->setParameters($parameters)
                ->getQuery()
                ->getResult()
        );
    }

    public function getActivitiesPersonDate(int $personId, \DateTime $date)
    {
        $qb = $this->createQueryBuilder('a')
            ->innerJoin('a.persons', 'ap')
            ->leftJoin('a.project', 'p')
            ->leftJoin('p.members', 'pp')
            ->where('(ap.person = :personId  OR pp.person = :personId) AND (a.dateStart < :date AND a.dateEnd >= :date)')
            ->setParameters(
                [
                    'personId' => "$personId",
                    'date' => $date
                ]
            );
        $result = $qb->getQuery()->getResult();

        return $result;
    }


    /**
     * Retourne la liste des activités à la période donnée.
     *
     * @param $periodeCodeStr
     * @return mixed
     */
    public function getActivitiesAtPeriod($periodeCodeStr)
    {
        $periodInfos = DateTimeUtils::periodBounds($periodeCodeStr);
        $query = $this->createQueryBuilder('a')
            ->where('a.dateStart <= :start AND a.dateEnd >= :end')
            ->setParameters(
                [
                    'start' => $periodInfos['start'],
                    'end' => $periodInfos['end'],
                ]
            );
        $activities = $query->getQuery()->getResult();
        return $activities;
    }

    /**
     * Retourne la liste des activités à la période donnée.
     *
     * @param $periodeCodeStr
     * @return mixed
     */
    public function getActivitiesAtPeriodWithWorkPackage($periodeCodeStr)
    {
        $periodInfos = DateTimeUtils::periodBounds($periodeCodeStr);
        $query = $this->createQueryBuilder('a')
            ->where('a.dateStart <= :start AND a.dateEnd >= :end')
            ->innerJoin('a.workPackages', 'w')
            ->setParameters(
                [
                    'start' => $periodInfos['start'],
                    'end' => $periodInfos['end'],
                ]
            );
        $activities = $query->getQuery()->getResult();
        return $activities;
    }

    public function getActivityByNumOscar($numOscar, $throw = false)
    {
        try {
            $activity = $this->findOneBy(['oscarNum' => $numOscar]);
            return $activity;
        } catch (\Exception $exception) {
            $error = "Impossible de charger l'activité $numOscar : " . $exception->getMessage();
            if ($throw) {
                throw new OscarException($error);
            } else {
                return null;
            }
        }
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function qbActivityWithWorkPackage()
    {
        $qb = $this->createQueryBuilder('a')
            ->innerJoin('a.workPackages', 'wp');
        return $qb;
    }

    /**
     * @return Activity[]
     */
    public function getActivitiesWithWorkPackageDatesMissing(): array
    {
        $qb = $this->qbActivityWithWorkPackage()
            ->where('a.dateStart IS NULL OR a.dateEnd IS NULL');
        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les activités où les dates de début/fin sont inversées.
     *
     * @return Activity[]
     */
    public function getActivitiesWithTimeParadox(): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.dateStart > a.dateEnd');
        return $qb->getQuery()->getResult();
    }
}