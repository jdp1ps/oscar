<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 28/08/18
 * Time: 09:15
 */

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oscar\Exception\OscarException;
use Oscar\Utils\DateTimeUtils;

/**
 * Class ActivityRepository
 * @package Oscar\Entity
 */
class ActivityRepository extends EntityRepository
{
    /**
     * Retourne l'ID des activités qui impliquent une des numérotations données.
     *
     * @param array $numerotations
     * @return array
     */
    public function getActivitiesIdsWithNumerotations(array $numerotations): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('DISTINCT a.id');

        if (count($numerotations) == 0 || (in_array('null', $numerotations) && count($numerotations) == 1)) {
            $queryBuilder->where('a.numbers = \'a:0:{}\' OR a.numbers = \'N;\' OR a.numbers IS NULL');
        } else {
            $where = [];
            foreach ($numerotations as $num) {
                $where[] = 'a.numbers LIKE \'%s:' . strlen($num) . ':"' . $num . '"%\'';
            }

            $queryBuilder->where(implode(' OR ', $where));
        }
        return array_map('current', $queryBuilder->getQuery()->getArrayResult());
    }


    protected function baseQueryWithOrganizationOf() :QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.id')
            ->leftJoin('c.project', 'pr')
            ->leftJoin('c.organizations', 'p1')
            ->leftJoin('p1.organization', 'orga1')
            ->leftJoin('pr.partners', 'p2')
            ->leftJoin('p2.organization', 'orga2');
    }

    /**
     * Retourne les IDS des activités impliquant une organisation d'un des types spécifiés.
     *
     * @param array $organizationTypeIds ID des types d'organisation
     * @return array
     */
    public function getIdsWithOrganizationOfType(array $organizationTypeIds): array
    {
        $queryBuilder = $this->baseQueryWithOrganizationOf()
            ->where('orga1.typeObj IN (:typeorga) OR orga2.typeObj IN (:typeorga)');

        $queryBuilder->setParameters(
            [
                'typeorga' => $organizationTypeIds
            ]
        );
        return array_map('current', $queryBuilder->getQuery()->getArrayResult());
    }

    /**
     * Retourne les IDS des activités impliquant une organisation ayant un des pays spécifiés.
     *
     * @param array $organizationTypeIds ID des types d'organisation
     * @return array
     */
    public function getIdsWithOrganizationOfCountry( array $countries ): array {
        $queryBuilder = $this->baseQueryWithOrganizationOf()
            ->where('orga1.country IN (:countries) OR orga2.country IN (:countries)');

        $queryBuilder->setParameters(
            [
                'countries' => $countries
            ]
        );
        return array_map('current', $queryBuilder->getQuery()->getArrayResult());
    }

    /**
     * Retourne les IDS des activités qui impliquent un des types de documents donnés.
     *
     * @param array $idsTypeDocument
     * @return array
     */
    public function getActivitiesIdsWithTypeDocument(array $idsTypeDocument): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('DISTINCT a.id')
            ->innerJoin('a.documents', 'd')
            ->where('d.typeDocument IN(:typesDocument)')
            ->setParameter('typesDocument', $idsTypeDocument);

        return array_map('current', $queryBuilder->getQuery()->getArrayResult());
    }

    public function getActivitiesWithNumber(string $key): array
    {
        $len = strlen($key);
        $queryBuilder = $this->createQueryBuilder('a')
            ->where("a.numbers LIKE '%s:$len:\"" . $key . "\"%'");
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Retourne la liste des différents PFIs.
     *
     * @return array
     */
    public function getDistinctPFI(): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('DISTINCT a.codeEOTP');

        return array_map('current', $queryBuilder->getQuery()->getArrayResult());
    }

    public function getActivitiesIdsByPfis(array $pfis): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('a.id')
            ->where('a.codeEOTP IN(:pfis)')
            ->setParameter('pfis', $pfis);
        return array_map('current', $queryBuilder->getQuery()->getArrayResult());
    }

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
            ->where(
                '(ap.person = :personId  OR pp.person = :personId) AND (a.dateStart < :date AND a.dateEnd >= :date)'
            )
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

    public function getBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.project', 'p');
//            ->leftJoin('c.persons', 'm1')
//            ->leftJoin('m1.person', 'pers1')
//            ->leftJoin('c.disciplines', 'dis')
//            ->leftJoin('c.activityType', 't1')
//            ->leftJoin('c.organizations', 'p1')
//            ->leftJoin('p1.organization', 'orga1')
//            ->leftJoin('c.documents', 'd1')
//            ->leftJoin('c.project', 'pr')
//            ->leftJoin('pr.members', 'm2')
//            ->leftJoin('pr.partners', 'p2')
//            ->leftJoin('m2.person', 'pers2')
//            ->leftJoin('p2.organization', 'orga2');
    }

    public function getBaseQueryBuilderByIdsPaged(array $ids, int $page = 1, int $resultByPage = 50): QueryBuilder
    {
        $offsetSQL = ($page - 1) * $resultByPage;
        $limitSQL = $resultByPage;

        return $this->getBaseQueryBuilder()
            ->select('c')
            ->where('c.id in(:ids)')
            ->setFirstResult($offsetSQL)
            ->setMaxResults($limitSQL)
            ->setParameters(
                [
                    'ids' => $ids
                ]
            );
    }

    /**
     * @param int $idPerson
     * @param int $idRole
     * @return array
     */
    public function getIdsWithoutPersonWithRole(int $idPerson, int $idRole): array
    {
        $idsWith = $this->getIdsForPersonWithRole($idPerson, $idRole);
        $qb = $this->createQueryBuilder('a')
            ->select('a.id')
            ->where('a.id NOT IN(:ids)')
            ->setParameter('ids', $idsWith);
        return array_map(
            'current',
            $qb->getQuery()->getResult()
        );
    }

    public function getIdsForPersons(array $idsPersons): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.id')
            ->leftJoin('a.persons', 'act_per')
            ->leftJoin('a.project', 'prj')
            ->leftJoin('prj.members', 'prj_pers');

        $parameters = [
            'persons' => $idsPersons
        ];

        $qb->where('act_per.person IN(:persons) OR prj_pers.person IN(:persons)');

        return array_map(
            'current',
            $qb
                ->getQuery()
                ->setParameters($parameters)
                ->getResult()
        );
    }

    /**
     * Retourne la liste des IDS des activités où la personne est impliquée (avec le role).
     *
     * @param int $idPerson ID de la personne (Person)
     * @param int $idRole Si -1, ignoré
     *
     * @return array
     */
    public function getIdsForPersonWithRole(int $idPerson, int $idRole): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.id')
            ->leftJoin('a.persons', 'act_per')
            ->leftJoin('a.project', 'prj')
            ->leftJoin('prj.members', 'prj_pers');

        $parameters = [
            'person' => $idPerson
        ];

        if ($idRole > 0) {
            $qb->where(
                '(act_per.person = :person AND act_per.roleObj = :role) '
                . 'OR (prj_pers.person = :person AND prj_pers.roleObj = :role)'
            );
            $parameters['role'] = $idRole;
        } else {
            $qb->where('act_per.person = :person OR prj_pers.person = :person');
        }

        return array_map(
            'current',
            $qb
                ->getQuery()
                ->setParameters($parameters)
                ->getResult()
        );
    }

    /**
     * Retourne la liste des IDs des activités ayant un des status donné.
     *
     * @param array $status
     * @return array
     */
    public function getIdsWithStatus(array $status): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.id')
            ->where('a.status IN(:status)');

        $parameters = [
            'status' => $status
        ];

        return array_map(
            'current',
            $qb
                ->getQuery()
                ->setParameters($parameters)
                ->getResult()
        );
    }

    public function getBeetween2Dates(string $from, string $to, string $field): array
    {
        if (!$from && !$to) {
            throw new OscarException("Le filtrage par date implique des dates");
        }

        $qb = $this->createQueryBuilder('a')
            ->select('a.id');

        $parameters = [

        ];

        if ($from) {
            $qb->andWhere('a.' . $field . ' >= :from');
            $parameters['from'] = $from;
        }

        if ($to) {
            $qb->andWhere('a.' . $field . ' <= :to');
            $parameters['to'] = $to;
        }

        return array_map(
            'current',
            $qb
                ->getQuery()
                ->setParameters($parameters)
                ->getResult()
        );
    }

    public function getActivitiesIdsAll(): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.id');

        return array_map(
            'current',
            $qb
                ->getQuery()
                ->getResult()
        );
    }

    /**
     * Retourne la liste des ids des activités n'impliquant pas l'organisation (avec le rôle).
     *
     * @param int $idOrganization
     * @param int $idRole
     * @return array
     */
    public function getIdsWithoutOrganizationWithRole(int $idOrganization, int $idRole): array
    {
        $idsExclude = $this->getIdsForOrganizationWithRole($idOrganization, $idRole);
        $qb = $this->createQueryBuilder('a')
            ->select('a.id')
            ->where('a.id NOT IN(:not)')
            ->setParameter('not', $idsExclude);
        return array_map(
            'current',
            $qb
                ->getQuery()
                ->getResult()
        );
    }

    /**
     * @param int $idOrganization
     * @param int $idRole
     * @return array
     */
    public function getIdsForOrganizationWithRole(int $idOrganization, int $idRole): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.id')
            ->leftJoin('a.organizations', 'act_org')
            ->leftJoin('a.project', 'prj')
            ->leftJoin('prj.partners', 'prj_org')
            ->where(
                'act_org.organization = :organization OR prj_org.organization = :organization'
            );


        $parameters = [
            'organization' => $idOrganization
        ];

        if ($idRole > 0) {
            $qb->where(
                '(act_org.organization = :organization AND act_org.roleObj = :role) '
                . 'OR (prj_org.organization = :organization AND prj_org.roleObj = :role)'
            );
            $parameters['role'] = $idRole;
        } else {
            $qb->where('act_org.organization = :organization OR prj_org.organization = :organization');
        }

        return array_map(
            'current',
            $qb
                ->getQuery()
                ->setParameters($parameters)
                ->getResult()
        );
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


    public function getActivityIdsWithWorkpackage(): array
    {
        return array_map(
            'current',
            $this->getQueryActivityIdsWithWorkpackage()
                ->getQuery()
                ->getResult()
        );
    }

    public function getQueryActivityIdsWithWorkpackage(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.id')
            ->innerJoin('a.workPackages', 'wp');

        return $qb;
    }
}