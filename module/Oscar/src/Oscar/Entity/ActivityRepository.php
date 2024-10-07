<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 28/08/18
 * Time: 09:15
 */

namespace Oscar\Entity;

use \DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Exception;
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
        }
        else {
            $where = [];
            foreach ($numerotations as $num) {
                $where[] = 'a.numbers LIKE \'%s:' . strlen($num) . ':"' . $num . '"%\'';
            }

            $queryBuilder->where(implode(' OR ', $where));
        }
        return array_map('current', $queryBuilder->getQuery()->getArrayResult());
    }

    /**
     * @return QueryBuilder
     */
    /**
     * Retourne les activités avec des jalons en retard.
     * @return array
     */
    public function getActivitiesWithUndoneMilestones(string $dateRef = ""): array
    {
        if (!$dateRef) {
            $dateRef = date('Y-m-d');
        }

        $queryBuilder = $this->createQueryBuilder('a')
            ->innerJoin('a.milestones', 'm')
            ->innerJoin('m.type', 't')
            ->where('t.finishable = true AND m.dateStart < :dateRef AND (m.finished IS NULL OR m.finished < 100)');

        $pameters = [
            'dateRef' => $dateRef
        ];
        $queryBuilder->setParameters($pameters);
        return $queryBuilder->getQuery()->getResult();
    }


    protected function baseQueryWithOrganizationOf(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.id')
            ->leftJoin('c.project', 'pr')
            ->leftJoin('c.organizations', 'p1')
            ->leftJoin('p1.organization', 'orga1')
            ->leftJoin('pr.partners', 'p2')
            ->leftJoin('p2.organization', 'orga2');
    }

    public function getIdsWithOrganizations(array $organisationsIds): array
    {
        $queryBuilder = $this->baseQueryWithOrganizationOf()
            ->where('orga1.id IN (:organizations_ids) OR orga2.id IN (:organizations_ids)');

        $queryBuilder->setParameters(
            [
                'organizations_ids' => $organisationsIds
            ]
        );
        return array_map('current', $queryBuilder->getQuery()->getArrayResult());
    }

    public function getIdsProjectsWithOrganizations(array $organisationsIds): array
    {
        $queryBuilder = $this->baseQueryWithOrganizationOf()->select('DISTINCT pr.id')
            ->where('orga1.id IN (:organizations_ids) OR orga2.id IN (:organizations_ids)')
            ->andWhere('pr.id IS NOT NULL');

        $queryBuilder->setParameters(
            [
                'organizations_ids' => $organisationsIds
            ]
        );
        return array_map('current', $queryBuilder->getQuery()->getArrayResult());
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
     * @param array $countries
     * @return array
     */
    public function getIdsWithOrganizationOfCountry(array $countries): array
    {
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
     * Retourne les IDs des activités impliquant une des organizations donnée avec le rôle spécifié
     *
     * @param array $organizationsIds IDS des organisations
     * @param int $roleObjId ID rôle
     * @throws OscarException
     */
    public function getIdsWithOneOfOrganizationsRoled(array $organizationsIds, int $roleObjId = 0): array
    {
        if (count($organizationsIds) == 0 && $roleObjId <= 0) {
            throw new OscarException("Vous devez préciser une organisation et/ou un rôle");
        }

        $clauseA = [];
        $clauseB = [];
        $params = [];

        if ($roleObjId > 0) {
            $clauseA[] = 'p1.roleObj = :roleId';
            $clauseB[] = 'p2.roleObj = :roleId';
            $params['roleId'] = $roleObjId;
        }

        if (count($organizationsIds) > 0) {
            $clauseA[] = 'orga1.id IN(:orgIds)';
            $clauseB[] = 'orga2.id IN(:orgIds)';
            $params['orgIds'] = $organizationsIds;
        }

        $queryBuilder = $this->baseQueryWithOrganizationOf();
        $clause = '('
            . implode(' AND ', $clauseA)
            . ') OR ('
            . implode(' AND ', $clauseB)
            . ')';

        $queryBuilder->where($clause);
        $queryBuilder->setParameters($params);

        return array_map('current', $queryBuilder->getQuery()->getArrayResult());
    }

    /**
     * @param int $organizationId
     * @param int $roleObjId
     * @return array
     * @throws OscarException
     */
    public function getIdsWithOrganizationAndRole(int $organizationId = 0, int $roleObjId = 0): array
    {
        $organizationIds = [];
        if ($organizationId > 0) {
            $organizationIds[] = $organizationId;
        }

        return $this->getIdsWithOneOfOrganizationsRoled($organizationIds, $roleObjId);
    }

    /**
     * Retourne les IDS des activités qui impliquent un des types de documents donnés.
     *
     * @param array $typeDocumentIds
     * @param bool $reverse
     * @return array
     */
    public function getActivitiesIdsWithTypeDocument(array $typeDocumentIds, bool $reverse = false): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('DISTINCT a.id')
            ->innerJoin('a.documents', 'd');

        if (count($typeDocumentIds) > 0) {
            $queryBuilder->where('d.typeDocument IN(:typesDocument)');
            $queryBuilder->setParameter('typesDocument', $typeDocumentIds);
        }

        $ids = array_map('current', $queryBuilder->getQuery()->getArrayResult());

        if ($reverse) {
            $queryBuilder = $this->createQueryBuilder('a')
                ->select('DISTINCT a.id');
            if (count($ids) > 0) {
                $queryBuilder->where('a.id NOT IN(:ids)')
                    ->setParameter('ids', $ids);
            }

            $ids = array_map('current', $queryBuilder->getQuery()->getArrayResult());
        }

        return $ids;
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

    /**
     * @param array $pfis
     * @return array
     */
    public function getActivitiesIdsByPfis(array $pfis): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('a.id')
            ->where('a.codeEOTP IN(:pfis)')
            ->setParameter('pfis', $pfis);
        return array_map('current', $queryBuilder->getQuery()->getArrayResult());
    }

    /**
     * @return Activity[]
     */
    public function getActivitiesActive(): array
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
     * @param array $idsOrganization
     * @param bool $principal
     * @return array
     */
    public function getActivitiesIdsForOrganizations(array $idsOrganization, bool $principal = true): array
    {
        $query = $this->createQueryBuilder('a')
            ->select('a.id')
            ->innerJoin('a.organizations', 'oa')
            ->innerJoin('oa.roleObj', 'oar')
            ->where('oa.organization IN(:idsOrganization) AND oar.principal = :principal');

        $parameters = [
            'idsOrganization' => $idsOrganization,
            'principal'       => $principal
        ];

        return array_map(
            'current',
            $query->setParameters($parameters)
                ->getQuery()
                ->getResult()
        );
    }

    /**
     * @param int $personId
     * @param DateTime $date
     * @return array
     */
    public function getActivitiesPersonDate(int $personId, DateTime $date): array
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
                    'date'     => $date
                ]
            );
        return $qb->getQuery()->getResult();
    }


    /**
     * Retourne la liste des activités à la période donnée.
     *
     * @param $periodeCodeStr
     * @return mixed
     */
    public function getActivitiesAtPeriod($periodeCodeStr): array
    {
        $periodInfos = DateTimeUtils::periodBounds($periodeCodeStr);
        $query = $this->createQueryBuilder('a')
            ->where('a.dateStart <= :start AND a.dateEnd >= :end')
            ->setParameters(
                [
                    'start' => $periodInfos['start'],
                    'end'   => $periodInfos['end'],
                ]
            );
        return $query->getQuery()->getResult();
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
                    'end'   => $periodInfos['end'],
                ]
            );
        $activities = $query->getQuery()->getResult();
        return $activities;
    }

    public function getActivityByNumOscar($numOscar, $throw = false)
    {
        try {
            return $this->findOneBy(['oscarNum' => $numOscar]);
        } catch (Exception $exception) {
            $error = "Impossible de charger l'activité $numOscar : " . $exception->getMessage();
            if ($throw) {
                throw new OscarException($error);
            }
            else {
                return null;
            }
        }
    }

    /**
     * @return QueryBuilder
     */
    protected function qbActivityWithWorkPackage(): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.workPackages', 'wp');
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

    /**
     * @param array $idsOrganisations
     * @return int[]
     */
    public function getIdsForOrganizations(array $idsOrganisations): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.id')
            ->leftJoin('a.organizations', 'act_org')
            ->leftJoin('a.project', 'prj')
            ->leftJoin('prj.partners', 'prj_org');

        $parameters = [
            'organizations' => $idsOrganisations
        ];

        $qb->where('act_org.organization IN(:organizations) OR prj_org.organization IN(:organizations)');

        return array_map(
            'current',
            $qb
                ->getQuery()
                ->setParameters($parameters)
                ->getResult()
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
        }
        else {
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

    /**
     * @return integer[]
     */
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
        }
        else {
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

    /**
     * @return integer[]
     */
    public function getProjectsIdsAll(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.id');

        return array_map(
            'current',
            $qb
                ->getQuery()
                ->getResult()
        );
    }

    /**
     * Retourne la liste des IDS des activités ayant un numoscar approchant.
     *
     * @param string $oscarNumLike
     * @return integer[]
     */
    public function getActivityIdsByOscarNumLike(string $oscarNumLike): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.id')
            ->where('a.oscarNum LIKE :oscarnum')
            ->setParameter('oscarnum', $oscarNumLike);

        return array_map(
            'current',
            $qb
                ->getQuery()
                ->getResult()
        );
    }

    /**
     * Retourne la liste des IDS des projets ayant l'activité correspondante
     *
     * @param string $oscarNumLike
     * @return integer[]
     */
    public function getProjectIdsByOscarNumLike(string $oscarNumLike): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT p.id')
            ->from(Project::class, 'p')
            ->innerJoin('p.grants', 'a')
            ->where('a.oscarNum LIKE :oscarnum')
            ->setParameter('oscarnum', $oscarNumLike);

        return array_map(
            'current',
            $qb
                ->getQuery()
                ->getResult()
        );
    }

    /**
     * @param string $pfi
     * @return integer[]
     */
    public function getActivityIdsByPFI(string $pfi): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT a.id')
            ->from(Activity::class, 'a')
            ->where('LOWER(a.codeEOTP) = LOWER(:pfi)')
            ->setParameter('pfi', $pfi);

        return array_map(
            'current',
            $qb
                ->getQuery()
                ->getResult()
        );
    }

    /**
     * @param string $pfi
     * @return integer[]
     */
    public function getProjectIdsByPFI(string $pfi): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.id')
            ->where('LOWER(a.codeEOTP) = LOWER(:pfi)')
            ->setParameter('pfi', $pfi);

        return array_map(
            'current',
            $qb
                ->getQuery()
                ->getResult()
        );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Permet d'obtenir la liste des IDS (Activités/Projets) ou la personne et/ou le rôle est impliqué.
     *
     * @param array $idPersons
     * @param int $idRole
     * @return QueryBuilder
     * @throws OscarException
     */
    protected function getQueryIdsForPersonOrWithRole(array $idPersons, int $idRole): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.persons', 'act_per')
            ->leftJoin('a.project', 'prj')
            ->leftJoin('prj.members', 'prj_pers');

        // PERSONNES et ROLE
        $parameters = [];

        if (count($idPersons) > 0 && $idRole > 0) {
            $qb->andWhere(
                '(act_per.person IN(:person) AND act_per.roleObj = :role) OR (prj_pers.person IN(:person) AND prj_pers.roleObj = :role)'
            );
            $parameters['role'] = $idRole;
            $parameters['person'] = $idPersons;
        }
        elseif (count($idPersons) > 0) {
            $qb->andWhere('act_per.person IN(:person) OR prj_pers.person IN(:person)');
            $parameters['person'] = $idPersons;
        }
        elseif ($idRole > 0) {
            $qb->andWhere('act_per.roleObj = :role OR prj_pers.roleObj = :role');
            $parameters['role'] = $idRole;
        }
        else {
            throw new OscarException("Critère de requête incomplet");
        }

        $qb->setParameters($parameters);

        return $qb;
    }

    /**
     * @param array $idPersons
     * @param int $idRole
     * @param bool $not
     * @return array
     * @throws OscarException
     */
    public function getIdsForPersonAndOrWithRole(array $idPersons, int $idRole, bool $not = false): array
    {
        return array_map(
            'current',
            $this->getQueryIdsForPersonOrWithRole($idPersons, $idRole, $not)->select('a.id')
                ->getQuery()->getResult()
        );
    }

    /**
     * @param $idPersons
     * @param int $idRole
     * @return array
     * @throws OscarException
     */
    public function getIdsProjectsForPersonAndOrWithRole($idPersons, int $idRole): array
    {
        return array_map(
            'current',
            $this->getQueryIdsForPersonOrWithRole($idPersons, $idRole)->select('DISTINCT prj.id')
                ->getQuery()->getResult()
        );
    }

    /**
     * Retourne les IDs des projets pour la liste des IDs d'activité donnée.
     * @param array|null $ids
     * @param string $orderBy
     * @param string $direction
     * @return array
     */
    public function getIdsProjectsForActivity(
        ?array $ids,
        string $orderBy = "",
        string $direction = 'desc',
        bool $ignoreNull = false
    ): array {
        if ($ids) {
            $qb = $this->getEntityManager()->createQueryBuilder()
                ->select(
                    'DISTINCT p.id, 
                    MIN(a.dateCreated) as dateCreated, 
                    MIN(a.dateStart) as dateStart,
                    MAX(a.dateEnd) as dateEnd,
                    MAX(a.dateUpdated) as dateUpdated,
                    MAX(a.dateSigned) as dateSigned, 
                    MAX(a.dateOpened) as dateOpened
                    '
                )
                ->from(Project::class, 'p')
                ->leftJoin('p.grants', 'a')
                ->where('a.id IN(:ids)')
                ->groupBy('p.id')
                ->setParameter('ids', $ids);

            $orderable = ['dateCreated', 'dateUpdated', 'dateEnd', 'dateStart', 'dateSigned', 'dateOpened'];
            if ($orderBy && in_array($orderBy, $orderable)) {
                $qb->orderBy($orderBy, $direction);
            }

            $results = $qb->getQuery()->getResult();

            return array_map('current', $results);
        }
        else {
            return [];
        }
    }

    public function getIdsProjectsForActivityAndEmpty(
        string $search,
        ?array $ids,
        string $orderBy = "",
        string $direction = 'desc',
        bool $ignoreNull = false
    ): array {
        if ($ids) {
            $qb = $this->getEntityManager()->createQueryBuilder()
                ->select(
                    'DISTINCT p.id as projectId,
                    MIN(a.dateCreated) as dateCreated, 
                    MIN(a.dateStart) as dateStart,
                    MAX(a.dateEnd) as dateEnd,
                    MAX(a.dateUpdated) as dateUpdated,
                    MAX(a.dateSigned) as dateSigned, 
                    MAX(a.dateOpened) as dateOpened
                    '
                )
                ->from(Project::class, 'p')
                ->leftJoin('p.grants', 'a')
                ->where('a.id IN(:ids)')
                ->groupBy('p.id');

// TODO TROUVER UNE AUTRE SOLUTION, sinon, des projets vides remontent sans tenir compte des filtres
//            if($search){
//                $qb->orWhere('(LOWER(p.acronym) LIKE LOWER(:search) OR LOWER(p.label) LIKE LOWER(:search) OR LOWER(p.description) LIKE LOWER(:search))')
//                    ->setParameter('search', '%' . $search . '%');
//            }

            $qb->setParameter('ids', $ids);

            $orderable = ['dateCreated', 'dateUpdated', 'dateEnd', 'dateStart', 'dateSigned', 'dateOpened'];
            if ($orderBy && in_array($orderBy, $orderable)) {
                $qb->orderBy($orderBy, $direction);
            }

            $results = $qb->getQuery()->getResult();

            return array_map('current', $results);
        }
        else {
            return [];
        }
    }



    /**
     * Retourne la liste des IDS inverse
     * @param array $ids
     * @return array
     */
    public function getIdsInverse(array $ids): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT c.id')
            ->from(Activity::class, 'c');
        if (count($ids) > 0) {
            $qb->where('c.id NOT IN(:ids)')
                ->setParameter('ids', $ids);
        }

        return array_map('current', $qb->getQuery()->getResult());
    }

    /**
     * Liste des IDs dont le montant est entre Min et Max.
     * @param mixed $min
     * @param mixed $max
     * @return array
     */
    public function getIdsAmount(mixed $min, mixed $max)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT c.id')
            ->from(Activity::class, 'c');

        $parameters = [];

        if ($min !== null) {
            $qb->where('c.amount >= :min');
            $parameters['min'] = $min;
        }

        if ($max !== null) {
            $qb->where('c.amount >= :max');
            $parameters['max'] = $max;
        }


        return array_map('current', $qb->getQuery()->setParameters($parameters)->getResult());
    }

    /**
     * Liste des ID avec une des disciplines
     * @param array $disciplines
     * @return array
     */
    public function getIdsDisciplines(array $disciplines): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT c.id')
            ->from(Activity::class, 'c')
            ->innerJoin('c.disciplines', 'd')
            ->where('d IN (:disciplines)')
            ->setParameter('disciplines', $disciplines);
        return array_map('current', $qb->getQuery()->getResult());
    }

    /**
     * @param int $milestoneId (DateType->id)
     * @param array|null $progression
     * @return array
     */
    public function getIdsMilestone(int $milestoneId, ?array $progression): array
    {
        $q = $this->createQueryBuilder('c')
            ->select('c.id')
            ->innerJoin('c.milestones', 'm')
            ->where('m.type = :jalonId');

        if (is_array($progression) && count($progression) > 0) {
            $clause = 'm.finished IN(:progression)';

            if (in_array('0', $progression)) {
                $clause .= ' OR m.finished IS NULL';
            }
            $q->andWhere($clause)
                ->setParameter('progression', $progression);
        }

        $q->setParameter('jalonId', $milestoneId);

        $activities = $q->getQuery()->getResult();
        return array_map('current', $activities);
    }

    /**
     * @param string $field
     * @param mixed $value1
     * @param mixed $value2
     * @return integer[]
     * @throws Exception
     */
    public function getIdsByDate(string $field, mixed $value1, mixed $value2): array
    {
        if (!$value1 && !$value2) {
            throw new Exception("Aucune date à filtrer");
        }
        $q = $this->createQueryBuilder('c')
            ->select('c.id');

        $parameters = [];

        if ($value1) {
            $q->andWhere('c.' . $field . ' >= :from');
            $parameters['from'] = $value1;
        }
        if ($value2) {
            $q->andWhere('c.' . $field . ' <= :to');
            $parameters['to'] = $value2;
        }


        $q->setParameters($parameters);

        $activities = $q->getQuery()->getResult();
        return array_map('current', $activities);
    }

    /**
     * Retourne les IDS des activités en fonction de l'impact financier.
     * @param mixed $indexImpact Index dans la liste des impacts possibles
     * @param bool $inverse
     * @return array
     * @throws Exception
     */
    public function getIdsFinancialImpact(mixed $indexImpact, bool $inverse = false): array
    {
        if (!array_key_exists($indexImpact, Activity::getFinancialImpactValues())) {
            throw new Exception("Ce type d'incidence financière n'existe pas");
        }

        $param = Activity::getFinancialImpactValues()[$indexImpact];

        $q = $this->createQueryBuilder('c')
            ->select('c.id');

        if ($inverse) {
            $q->andWhere('c.financialImpact != :param');
        }
        else {
            $q->where('c.financialImpact = :param');
        }

        $q->setParameter('param', $param);

        return array_map('current', $q->getQuery()->getResult());
    }

    /**
     * Retourne les IDS des activités sans projet.
     * @return integer[]
     */
    public function getIdsWithoutProject(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.id')
            ->leftJoin('c.project', 'p')
            ->where('c.project IS NULL');;
        return array_map('current', $qb->getQuery()->getResult());
    }

    /**
     * Retourne les IDS des activités d'un des types.
     * @param array $types
     * @param bool $inverse
     * @return array
     */
    public function getIdsWithTypes(array $types, bool $inverse = false): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.id')
            ->leftJoin('c.activityType', 't');

        if ($inverse === false) {
            $qb->where('c.activityType IN(:types)');
        }
        else {
            $qb->where('c.activityType NOT IN (:types)');;
        }
        return array_map('current', $qb->setParameter('types', $types)->getQuery()->getResult());
    }

    /**
     * @param array $activitiesIds
     * @param mixed $sort
     * @param mixed $sortDirection
     * @param bool $ignoreNull
     * @return integer[]
     */
    public function getIdsOrderedBy(array $activitiesIds, mixed $sort, mixed $sortDirection, bool $ignoreNull): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.id')
            ->orderBy('c.' . $sort, $sortDirection)
            ->where('c.id IN(:ids)')
            ->setParameter('ids', $activitiesIds);
        if ($ignoreNull) {
            $qb->andWhere("c." . $sort . " IS NOT NULL");
        }
        return array_map('current', $qb->getQuery()->getResult());
    }
}