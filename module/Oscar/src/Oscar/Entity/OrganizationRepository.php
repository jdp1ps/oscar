<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-23 13:54
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Oscar\Connector\IConnectedRepository;
use Oscar\Import\Activity\FieldStrategy\FieldImportOrganizationStrategy;
use Oscar\Import\Data\DataExtractorOrganization;

class OrganizationRepository extends EntityRepository implements IConnectedRepository
{
    /**
     * @param int $organizationId
     * @return Organization
     */
    public function getOrganisationById(int $organizationId): Organization
    {
        return $this->findOneBy(['id' => $organizationId]);
    }

    public function getUidsConnector(string $connectorName): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $search = sprintf('s:%s:"%s";', strlen($connectorName), $connectorName);

        $qb->select('o.connectors')
            ->from(Organization::class, 'o')
            ->where('o.connectors LIKE :search')
            ->setParameter('search', "%$search%");

        $uids = [];
        foreach ($qb->getQuery()->getArrayResult() as $a) {
            if( array_key_exists('connectors', $a) && $a['connectors'][$connectorName]) {
                $uids[] = $a['connectors'][$connectorName];
            }
        }
        return $uids;
    }

    /**
     * Retourne les sous-organizations de l'organisation donnée.
     *
     * @param int $organizationParentId
     * @return Organization[]
     */
    public function getSubOrganizations(int $organizationParentId): array
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.parent = :organizationParentId')
            ->setParameter('organizationParentId', $organizationParentId);
        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne la liste des organizations de la personnes.
     *
     * @param int $personId ID de la personne
     * @param bool $principal Uniquement les rôles marqués comme "principal"
     * @param mixed $date True = Date du jour, False = ignoré, Datetime = à la date donnée
     * @return Organization[]
     */
    public function getOrganizationsPerson(int $personId, bool $principal = false, $date = false): array
    {
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.persons', 'op')
            ->where('op.person = :person')
            ->setParameter('person', $personId);

        if ($date !== false) {
            $date = $date === true ? new \DateTime() : $date;
            $qb->andWhere('op.dateStart IS NULL OR op.dateStart <= :date');
            $qb->andWhere('op.dateEnd >= :date OR op.dateEnd IS NULL');
            $qb->setParameter('date', $date);
        }

        if ($principal === true) {
            $qb->innerJoin('op.roleObj', 'r')
                ->andWhere('r.principal = true');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les IDS des organisations où la personne est impliquée avec un rôle principale.
     *
     * @param $personId
     * @param bool $principale
     * @return array
     */
    public function getOrganizationsIdsForPerson($personId, $principale = true)
    {
        $parameters = [
            'personId' => $personId
        ];

        $query = $this->createQueryBuilder('o')
            ->select('o.id')
            ->innerJoin('o.persons', 'op')
            ->innerJoin('op.roleObj', 'opr')
            ->where('op.person = :personId');

        if ($principale === true) {
            $parameters['principale'] = true;
            $query->andWhere('opr.principal = :principale');
        }

        $query->setParameters($parameters);


        $result = $query->getQuery()->getResult();
        return array_map('current', $result);
    }

    /**
     * @param array $countries
     * @return array
     */
    public function getIdWithCountries(array $countries): array
    {
        $parameters = [
            'countries' => $countries
        ];

        $query = $this->createQueryBuilder('o')
            ->select('o.id')
            ->where('o.country IN(:countries)');


        $query->setParameters($parameters);

        $result = $query->getQuery()->getResult();
        return array_map('current', $result);
    }

    /**
     * Retourne les IDS des structures ayant pour type.
     *
     * @param array $typesIDs
     * @return array
     */
    public function getIdWithTypes(array $typesIDs): array
    {
        $parameters = [
            'types' => $typesIDs
        ];

        $query = $this->createQueryBuilder('o')
            ->select('o.id')
            ->where('o.typeObj IN(:types)');


        $query->setParameters($parameters);

        $result = $query->getQuery()->getResult();
        return array_map('current', $result);
    }


    public function saveOrganizationPerson(Person $person, Organization $organisation, $roleOscarId)
    {
        $personOrganization = new OrganizationPerson();
        $this->getEntityManager()->persist($personOrganization);
        $personOrganization->setPerson($person)
            ->setOrganization($organisation)
            ->setRoleObj($this->getEntityManager()->getRepository(Role::class)->find($roleOscarId));
        $this->getEntityManager()->flush($personOrganization);
    }

    public function getTypeObjByLabel($label)
    {
        return $this->getEntityManager()->getRepository(OrganizationType::class)->findOneBy(['label' => $label]);
    }

    /**
     * @param $fullName
     * @return Organization
     */
    public function createFromFullName($fullName)
    {
        $organisation = new Organization();
        $this->getEntityManager()->persist($organisation);
        $datas = (new DataExtractorOrganization())->extract($fullName);
        $organisation->setCode($datas['code'])
            ->setFullName($fullName)
            ->setShortName($datas['shortname']);
        $this->getEntityManager()->flush($organisation);
        return $organisation;
    }

    public function getOrganisationByNameOrCreate($fullName)
    {
        try {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('o')
                ->from(Organization::class, 'o')
                ->where('o.shortName = :name OR o.fullName = :name')
                ->setParameter('name', $fullName);
            $organizations = $qb->getQuery()->getResult();
            if (count($organizations) == 0) {
                return $this->createFromFullname($fullName);
            }
            return $organizations[0];
        } catch (\Exception $e) {
            echo "Can't create or get Org $fullName \n";
            return null;
        }
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws NoResultException
     */
    public function getObjectByConnectorID($connectorName, $connectorID)
    {
        return $this->getOrganizationByConnectorQuery($connectorName, $connectorID)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param string $code
     * @return Organization
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOrganisationByCode(string $code): Organization
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('o')
            ->from(Organization::class, 'o')
            ->where('o.code = :code')
            ->setParameter('code', $code);
        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param array $codes
     * @return Organization[]
     */
    public function getOrganisationsByCodes(array $codes): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('o')
            ->from(Organization::class, 'o')
            ->where('o.code IN(:codes)')
            ->setParameter('codes', $codes);
        return $qb->getQuery()->getResult();
    }

    public function newPersistantObject()
    {
        $obj = new Organization();
        $this->getEntityManager()->persist($obj);
        return $obj;
    }

    public function flush($mixed)
    {
        $this->getEntityManager()->flush($mixed);
    }

    /**
     * @param $connector
     * @param $value
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrganizationByConnectorQuery($connector, $value)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('o')
            ->from(Organization::class, 'o')
            ->where('o.connectors LIKE :search')
            ->setParameter('search', '%"' . $connector . '";s:' . strlen($value) . ':"' . $value . '";%');
        return $qb;
    }

    /**
     * @param $code
     * @return Organization|null
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOrganizationByCodePCRU($code): ?Organization
    {
        $qb = $this->getEntityManager()->createQueryBuilder('o')
            ->select('o')
            ->from(Organization::class, 'o')
            ->where('o.siret = :code OR o.duns = :code OR o.tvaintra = :code')
            ->getQuery()
            ->setParameter('code', $code);
        try {
            return $qb->getSingleResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getOrganizationsWithRnsr(): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder('o')
            ->select('o')
            ->from(Organization::class, 'o')
            ->where("o.rnsr IS NOT NULL AND o.rnsr != ''")
            ->getQuery();

        return $qb->getResult();
    }

    public function getTypesKeyLabel(): array
    {
        $types = $this->getEntityManager()->getRepository(OrganizationType::class)->findAll();
        $out = [];
        /** @var OrganizationType $organizationType */
        foreach ($types as $organizationType) {
            $out[$organizationType->getLabel()] = $organizationType;
        }
        return $out;
    }


    /**
     * @param int $organizationId
     * @return Organization[]
     */
    public function getOrganizationAndParents(int $organizationId): array
    {
        $out = [];
        /** @var Organization $organization */
        $organization = $this->find($organizationId);
        $out[$organization->getId()] = $organization;
        if ($organization->hasParent()) {
            foreach ($organization->hasParent() as $org) {
                $out[$org->getId()] = $org;
            }
        }
        return $out;
    }

    public function removeOrganizationById(int $organizationId)
    {
        $this->getEntityManager()->remove($this->find($organizationId));
        $this->getEntityManager()->flush();
    }

    /**
     * Retourne les IDs des organisations qui ont le role $from dans une activité ou un projet.
     * @param OrganizationRole $from
     * @return array
     */
    public function getOrganizationsIdWithRole(OrganizationRole $from) :array
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o.id')
            ->leftJoin('o.projects', 'p')
            ->leftJoin('o.activities', 'a')
            ->where('p.roleObj = :role OR a.roleObj = :role');
        $qb->setParameter('role', $from);
        $result = $qb->getQuery()->getResult();
        return array_column($result, 'id');
    }
}