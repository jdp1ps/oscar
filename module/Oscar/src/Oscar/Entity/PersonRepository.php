<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Oscar\Connector\IConnectedRepository;
use Oscar\Exception\OscarException;
use Oscar\Import\Data\DataExtractorFullname;

/**
 * Class ProjectGrantRepository
 * @package Oscar\Entity
 */
class PersonRepository extends EntityRepository implements IConnectedRepository
{
    private $_cacheSelectebleRolesOrganisation;

    public function getPersonsIdsForActivitiesids( $activitiesIds ){
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
    public function getPersonIdsInOrganizations( $organisationIds ){
        $result = $this->createQueryBuilder('p')
            ->select('p.id')
            ->innerJoin('p.organizations', 'op')
            ->where('op.organization IN(:organisationsIds)')
            ->setParameter('organisationsIds', $organisationIds)
            ->getQuery()
            ->getResult();
        return array_map('current', $result);
    }

    public function getPersonIdsForOrganizationsActivities( $organizationIds ){
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

    public function getSubordinates( $idReferent ){
        $ids = $this->getSubordinatesIds($idReferent);
        return $this->createQueryBuilder('p')
            ->where('p.id IN(:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
        ;

    }

    public function getSubordinatesIds( $referentId ){
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
    public function getPersonsWithUnreadNotificationsAndAuthentification(){
        $qb = $this->createQueryBuilder('p');
        $persons = $qb
            ->select('p')
            ->innerJoin(Authentification::class, 'a', Join::WITH, $qb->expr()->eq('p.ladapLogin', 'a.username'))
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
        return $this->getEntityManager()->getRepository(Role::class)->getRolesAtOrganizationArray();
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
        $qb->select('p')
            ->from(Person::class, 'p')
            ->where('p.connectors LIKE :search')
            ->setParameter('search', '%"' . $connector . '";s:'.strlen($value).':"' . $value . '";%');
        return $qb;
    }


    public function getRolesLdapUsed()
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT DISTINCT r.ldapFilter, r.roleId, r.principal FROM ' . Role::class . ' r WHERE r.ldapFilter IS NOT NULL');
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

    public function getObjectByConnectorID($connectorName, $connectorID)
    {
        return $this->getPersonByConnectorID($connectorName, $connectorID);
    }

    public function newPersistantObject()
    {
        return $this->newPersistantPerson();
    }


}