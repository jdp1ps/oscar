<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Oscar\Connector\IConnectedRepository;
use Oscar\Import\Data\DataExtractorFullname;
use Oscar\Utils\DateTimeUtils;
use Oscar\Utils\PeriodInfos;

/**
 * Class ProjectGrantRepository
 * @package Oscar\Entity
 */
class PersonRepository extends EntityRepository implements IConnectedRepository
{
    private $_cacheSelectebleRolesOrganisation;

    public function removeOrganizationPersons( array $organizationsRoles ):void
    {
        foreach ($organizationsRoles as $organizationsRole) {
            $this->getEntityManager()->remove($organizationsRole);
        }
    }


    public function getReferentsIdsPerson(int $person): array
    {
        try {
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('referent_id', 'referent_id');
            $sql = 'SELECT referent_id FROM referent WHERE person_id = :person';
            $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
            $results = $query->setParameter('person', $person)->getResult();
            return array_map('current', $results);

        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    public function removeReferent(int $removedReferentPersonId, int $subordinatePersonId): void
    {

        try {
            $query = $this->getEntityManager()->createQuery(
                'DELETE Oscar\Entity\Referent r 
            WHERE r.person = :subordinate AND r.referent = :referent'
            );

            $query->setParameters(
                [
                    'referent' => $removedReferentPersonId,
                    'subordinate' => $subordinatePersonId
                ]
            );

            $query->execute();

        } catch (\Exception $e) {
            die($e->getMessage());
        }

    }


    public function removePersonById(int $personId)
    {
        $sql = "DELETE FROM person WHERE id = :id";
        $params = array('id' => $personId);
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        return $stmt->execute($params);
    }


    public function removePerson(Person $person)
    {
        $this->getEntityManager()->remove($person);
    }

    public function removePersons(array $ids)
    {
        return $this->createQueryBuilder('p')->delete()->where('p.id IN(:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }

    /**
     * @param array $ids
     * @return Person[]
     */
    public function getPersonsByIds(array $ids)
    {
        $qb = $this->getBaseQuery()
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids);
        return $qb->getQuery()->getResult();
    }


    /**
     * Retourne la liste des UIDS des personnes issues d'une synchronisation dans
     * Oscar pour le connector $connector.
     *
     * @param $connector
     * @return array
     */
    public function getUidsConnector($connector)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $search = sprintf('s:%s:"%s";', strlen($connector), $connector);

        $qb->select('p.connectors')
            ->from(Person::class, 'p')
            ->where('p.connectors LIKE :search')
            ->setParameter('search', "%$search%");

        $uids = [];
        foreach ($qb->getQuery()->getArrayResult() as $a) {
            $uids[] = $a['connectors'][$connector];
        }
        return $uids;
    }

    public function getPersonsIdsForActivitiesids($activitiesIds)
    {
        $queryActivity = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->innerJoin('p.activities', 'ap')
            ->innerJoin('ap.activity', 'a')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $activitiesIds);

        $queryProject = $this->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->innerJoin('p.projectAffectations', 'pra')
            ->innerJoin('pra.project', 'pr')
            ->innerJoin('pr.grants', 'a')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $activitiesIds);

        $fromActivities = array_map('current', $queryActivity->getQuery()->getResult());
        $fromProject = array_map('current', $queryProject->getQuery()->getResult());

        return array_unique(array_merge($fromActivities, $fromProject));
    }

    /**
     * Retourne la liste des IDS des personnes identifiées directement comme membre dans la/les organisations.
     *
     * @param $organisationIds
     * @return array
     */
    public function getPersonIdsInOrganizations($organisationIds)
    {
        $result = $this->createQueryBuilder('p')
            ->select('p.id')
            ->innerJoin('p.organizations', 'op')
            ->where('op.organization IN(:organisationsIds)')
            ->setParameter('organisationsIds', $organisationIds)
            ->getQuery()
            ->getResult();
        return array_map('current', $result);
    }

    public function getPersonIdsForOrganizationsActivities($organizationIds)
    {
        $activityMembers = $this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->innerJoin('p.activities', 'ap')
            ->innerJoin('ap.activity', 'a')
            ->innerJoin('a.organizations', 'o')
            ->innerJoin('o.roleObj', 'r')
            ->where('r.principal = \'true\' AND o.organization IN(:organizationIds)')
            ->setParameter('organizationIds', $organizationIds)
            ->getQuery();

        $projectMembers = $this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')
            ->select('DISTINCT p.id')
            ->innerJoin('p.projectAffectations', 'p_p')
            ->innerJoin('p_p.project', 'prj')
            ->innerJoin('prj.partners', 'partner')
            ->innerJoin('partner.organization', 'org')
            ->innerJoin('partner.roleObj', 'r')
            ->where('r.principal = \'true\' AND org.id IN(:organizationIds)')
            ->setParameter('organizationIds', $organizationIds)
            ->getQuery();

        $idsFromActivities = array_map('current', $activityMembers->getResult());
        $idsFromProjects = array_map('current', $projectMembers->getResult());

        return array_unique(array_merge($idsFromActivities, $idsFromProjects));
    }

    public function getSubordinates($idReferent)
    {
        $ids = $this->getSubordinatesIds($idReferent);
        return $this->createQueryBuilder('p')
            ->where('p.id IN(:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    public function getSubordinatesIds($referentId)
    {
        $result = $this->getEntityManager()->getRepository(Referent::class)->createQueryBuilder('r')
            ->select('p.id')
            ->innerJoin('r.person', 'p')
            ->where('r.referent = :idReferent')
            ->setParameter('idReferent', $referentId)
            ->getQuery()
            ->getResult();
        return array_map('current', $result);
    }

    /**
     * Retourne la liste des personnes qui ont des notifications non-lues et ayant un compte d'authentification.
     *
     * @return mixed
     */
    public function getPersonsWithUnreadNotificationsAndAuthentification($normalize = false)
    {
        $qb = $this->createQueryBuilder('p');
        $persons = $qb
            ->select('p')
            ->innerJoin(
                Authentification::class,
                'a',
                Join::WITH,
                $qb->expr()->eq(
                    $normalize ? 'lower(p.ladapLogin)' : 'p.ladapLogin',
                    $normalize ? 'lower(a.username)' : 'a.username'
                )
            )
            ->innerJoin(NotificationPerson::class, 'n', Join::WITH, $qb->expr()->eq('p.id', 'n.person'))
            ->where('n.read IS NULL')
            ->getQuery()
            ->getResult();

        return $persons;
    }

    function getPersonByDisplayName($displayName)
    {
        /** @var Query $queryPerson */
        static $queryPerson;
        if ($queryPerson === null) {
            $queryPerson = $this->createQueryBuilder('p')
                ->where('CONCAT(p.firstname, \' \', p.lastname) = :displayName')
                ->getQuery();
        }
        return $queryPerson->setParameter('displayName', $displayName)->getResult();
    }

    /**
     * Recherche dans Oscar une personne à partir du nom complet.
     *
     * @param $displayName
     * @return null
     */
    public function getPersonByDisplayNameOrCreate($displayName)
    {
        $person = $this->getPersonByDisplayName($displayName);
        if (!$person) {
            $person = new Person();
            $this->getEntityManager()->persist($person);
            $fullname = (new DataExtractorFullname())->extract($displayName);
            if ($fullname) {
                $person->setFirstname($fullname['firstname'])
                    ->setLastname($fullname['lastname'])
                    ->setEmail($fullname['email']);

                $this->getEntityManager()->flush($person);
            } else {
                return null;
            }
        } else {
            return $person[0];
        }
    }

    /**
     * Retourne la liste des rôles dans les organisations pour la création de select.
     */
    public function getSelectableRolesOrganization()
    {
        if ($this->_cacheSelectebleRolesOrganisation === null) {
            $this->_cacheSelectebleRolesOrganisation = [];
            /** @var RoleOrganization $roleOrganization */
            foreach ($this->getRolesOrganization() as $roleOrganization) {
                $this->_cacheSelectebleRolesOrganisation[$roleOrganization->getId()] = $roleOrganization->getRoleId();
            }
        }
        return $this->_cacheSelectebleRolesOrganisation;
    }

    public function getRolesOrganizationArray()
    {
        return $this->getEntityManager()->getRepository(Role::class)->getRolesAvailableForPersonInOrganizationArray();
    }


    /**
     * Retourne la liste des rôles dans les organisations.
     */
    public function getRolesOrganization()
    {
        return $this->getEntityManager()->getRepository(RoleOrganization::class)->findAll();
    }


    public function flush($mixed)
    {
        $this->getEntityManager()->flush($mixed);
    }

    /**
     * @return Person
     * @throws \Doctrine\ORM\ORMException
     */
    public function newPersistantPerson()
    {
        $person = new Person();
        $this->getEntityManager()->persist($person);
        return $person;
    }

    /**
     * @param $email
     * @return Person[]
     */
    public function getPersonByEmail($email)
    {
        static $personByEmail;
        if ($personByEmail == null) {
            $personByEmail = $this->getBaseQuery()->where('p.email = :email');
        }
        return $personByEmail->setParameter('email', $email)->getQuery()->getResult();
    }


    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBaseQuery()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('pm, p, pj')
            ->from('Oscar\Entity\Person', 'p')
            ->leftJoin('p.projectAffectations', 'pm')
            ->leftJoin('pm.project', 'pj');

        return $qb;
    }

    public function search($search)
    {
        $query = $this->getBaseQuery();
        $query->orWhere('p.firstname LIKE :search')
            ->orWhere('p.lastname LIKE :search');
        return $query->getQuery()->execute(['search' => '%' . $search . '%']);
    }

    /**
     * Retourne la personne en fonction du connecteur.
     *
     * @param $connector
     * @param $value
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPersonByConnectorID($connector, $value)
    {
        return $this->getPersonByConnectorQuery($connector, $value)->getQuery()->getSingleResult();
    }

    /**
     * @param $connector
     * @param $value
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPersonsByConnectorID($connector, $value)
    {
        return $this->getPersonByConnectorQuery($connector, $value)->getQuery()->getResult();
    }

    /**
     * @param $connector
     * @param $value
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPersonByConnectorQuery($connector, $value)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $search = sprintf('s:%s:"%s";s:%s:"%s";', strlen($connector), $connector, strlen($value), $value);
        $qb->select('p')
            ->from(Person::class, 'p')
            ->where('p.connectors LIKE :search')
            ->setParameter('search', "%$search%");

        return $qb;
    }


    public function getRolesLdapUsed()
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT DISTINCT r.ldapFilter, r.roleId, r.principal FROM ' . Role::class . ' r WHERE r.ldapFilter IS NOT NULL'
        );
        $filtersUsed = [];
        foreach ($query->getResult() as $row) {
            $ldapFilter = preg_replace('/\(memberOf=(.*)\)/i', '$1', $row['ldapFilter']);
            $roleId = $row['roleId'];

            if (!array_key_exists($ldapFilter, $filtersUsed)) {
                $filtersUsed[$ldapFilter] = [];
            }
            $filtersUsed[$ldapFilter][] = [
                'label' => $roleId,
                'principal' => $row['principal']
            ];
        }
        return $filtersUsed;
    }

    public function getIdsValidatorsProject(bool $hasToValidate = false, int $year = -1, int $month = -1): array
    {
        $qb = $this->getBaseQueryValidator($year, $month)
            ->innerJoin('vp.validatorsPrj', 'p');

        if ($hasToValidate === true) {
            $qb->andWhere('vp.status = :step')
                ->setParameter('step', ValidationPeriod::STATUS_STEP1);
        }

        $ids = $qb->getQuery()->getArrayResult();

        return array_map('current', $ids);
    }

    public function getIdsValidatorsSci(bool $hasToValidate = false, int $year = -1, int $month = -1): array
    {
        $qb = $this->getBaseQueryValidator($year, $month)
            ->innerJoin('vp.validatorsSci', 'p');

        if ($hasToValidate === true) {
            $qb->andWhere('vp.status = :step')
                ->setParameter('step', ValidationPeriod::STATUS_STEP2);
        }

        $ids = $qb->getQuery()->getArrayResult();

        return array_map('current', $ids);
    }

    public function getIdsValidatorsAdm(bool $hasToValidate = false, int $year = -1, int $month = -1): array
    {
        $qb = $this->getBaseQueryValidator($year, $month)
            ->innerJoin('vp.validatorsAdm', 'p');

        if ($hasToValidate === true) {
            $qb->andWhere('vp.status = :step')
                ->setParameter('step', ValidationPeriod::STATUS_STEP3);
        }

        $ids = $qb->getQuery()->getArrayResult();

        return array_map('current', $ids);
    }


    public function getIdsValidators($hasValidating = true, $period = ""): array
    {
        $year = -1;
        $month = -1;
        if ($period != "") {
            $periodInfos = PeriodInfos::getPeriodInfosObj($period);
            $year = $periodInfos->getYear();
            $month = $periodInfos->getMonth();
        }

        $prj = $this->getIdsValidatorsProject($hasValidating, $year, $month);
        $sci = $this->getIdsValidatorsSci($hasValidating, $year, $month);
        $adm = $this->getIdsValidatorsAdm($hasValidating, $year, $month);

        $ids = array_unique(array_merge($sci, $adm, $prj));

        return $ids;
    }

    /**
     * Retourne la synthèse d'état des déclarations en cours de validation pour la personne.
     * (ATTENTION : Si aucune déclaration n'a été envoyée, il n'y a pas de résultats)
     * @param int $personId
     */
    public function getRepportDeclarationPerson( int $personId, bool $includenonActive = false )
    {
        $sql = "SELECT
            declarer_id AS declarer_id,
            vp.year || '-' || LPAD(vp.month::text, 2, '0') AS period,
            count(*) as Nbr,

            SUM(CASE WHEN  vp.validationactivityat IS NULL THEN 0 ELSE 1 END) AS prj,
            SUM(CASE WHEN  vp.validationsciat IS NULL THEN 0 ELSE 1 END) AS sci,
            SUM(CASE WHEN  vp.validationadmat IS NULL THEN 0 ELSE 1 END) AS adm,
        
            SUM(CASE WHEN  vp.rejectactivityat IS NULL THEN 0 ELSE 1 END) AS rejprj,
            SUM(CASE WHEN  vp.rejectsciat IS NULL THEN 0 ELSE 1 END) AS rejsci,
            SUM(CASE WHEN  vp.rejectadmat IS NULL THEN 0 ELSE 1 END) AS rejadm
        
            FROM validationperiod vp WHERE vp.declarer_id = :person_id 

            GROUP BY declarer_id, period
            ORDER BY period";

        $query = $this->getEntityManager()->getConnection()->prepare($sql);

        $result = $query->executeQuery([
            "person_id" => $personId
]       );

        $datas = $result->fetchAllAssociative();

        return $datas;
    }

    /**
     * Retourne les ID des déclarants avant la période donnée.
     *
     * @param string $period
     * @return array
     */
    public function getIdsDeclarersBeforePeriod( string $period, bool $includeNonActive = false ): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT(p.id) id')
            ->from(Person::class, 'p')
            ->innerJoin('p.workPackages', 'wpp')
            ->groupBy('p.id');

        $extract = DateTimeUtils::periodBounds($period);
        $end = $extract['end'];

        $qb->innerJoin('wpp.workPackage', 'wp')
            ->innerJoin('wp.activity', 'a')
            ->where('a.dateStart < :periodEnd');

        $parametersQuery = [
            'periodEnd' => $end
        ];

        if( $includeNonActive == false ){
            $qb->andWhere('a.status = :status');
            $parametersQuery['status'] = Activity::STATUS_ACTIVE;
        }

        $qb->setParameters($parametersQuery);

        $results = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        return array_map('current', $results);
    }

    /**
     * Retourne le liste des person.id des déclarants (pour la période si spécifiée).
     *
     * @param string|null $periodA (période, sous la forme YYYY-MM)
     * @return array
     */
    public function getIdsDeclarers(?string $period = null): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT(p.id) id')
            ->from(Person::class, 'p')
            ->innerJoin('p.workPackages', 'wpp')
            ->groupBy('p.id');

        $parametersQuery = [];

        // Traitement de la clause sur la période si besoin
        if ($period !== null) {
            // Filtrer sur la période
            $extract = DateTimeUtils::periodBounds($period);
            $start = $extract['start'];
            $end = $extract['end'];
            $qb->innerJoin('wpp.workPackage', 'wp')
                ->innerJoin('wp.activity', 'a')
                ->where('a.dateStart < :periodEnd AND a.dateEnd > :periodStart');
            $parametersQuery = [
                'periodEnd' => $end,
                'periodStart' => $start,
            ];
        }
        $qb->setParameters($parametersQuery);

        $results = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        return array_map('current', $results);
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Requête de base pour obtenir les PersonID des validateurs identifiés dans les procédures de validation.
     *
     * @param int $year Année ou -1 pour ignorer
     * @param int $month Mois (1=Janvier, 2=février, etc..  ou -1 pour ignorer)
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getBaseQueryValidator(int $year = -1, int $month = -1)
    {
        $qb = $this->getEntityManager()->createQueryBuilder('vp')
            ->select('DISTINCT(p.id)')
            ->from(ValidationPeriod::class, 'vp');

        if ($year > -1) {
            $qb->andWhere('vp.year = :year')
                ->setParameter('year', $year);
        }

        if ($month > -1) {
            $qb->andWhere('vp.month = :month')
                ->setParameter('month', $month);
        }
        return $qb;
    }

    public function getObjectByConnectorID($connectorName, $connectorID)
    {
        return $this->getPersonByConnectorID($connectorName, $connectorID);
    }

    public function newPersistantObject()
    {
        return $this->newPersistantPerson();
    }


}