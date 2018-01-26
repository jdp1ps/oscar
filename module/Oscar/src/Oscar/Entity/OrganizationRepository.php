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
    public function saveOrganizationPerson(Person $person, Organization $organisation, $roleOscarId) {
        $personOrganization = new OrganizationPerson();
        $this->getEntityManager()->persist($personOrganization);
        $personOrganization->setPerson($person)
            ->setOrganization($organisation)
            ->setRoleObj($this->getEntityManager()->getRepository(Role::class)->find($roleOscarId));
        $this->getEntityManager()->flush($personOrganization);
    }

    /**
     * @param $fullName
     * @return Organization
     */
    public function createFromFullName( $fullName ){
        $organisation = new Organization();
        $this->getEntityManager()->persist($organisation);
        $datas = (new DataExtractorOrganization())->extract($fullName);
        $organisation->setCode($datas['code'])
            ->setFullName($fullName)
            ->setShortName($datas['shortname']);
        $this->getEntityManager()->flush($organisation);
        return $organisation;
    }

    public function getOrganisationByNameOrCreate( $fullName ){
        try {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('o')
                ->from(Organization::class, 'o')
                ->where('o.shortName = :name OR o.fullName = :name')
                ->setParameter('name',  $fullName );
           $organizations = $qb->getQuery()->getResult();
           if( count($organizations) == 0 ){
               return $this->createFromFullname($fullName);
           }
           return $organizations[0];
        } catch (\Exception $e){
            echo "Can't create or get Org $fullName \n";
            return null;
        }

    }

    public function getObjectByConnectorID($connectorName, $connectorID)
    {
        return $this->getOrganizationByConnectorQuery($connectorName, $connectorID)
            ->getQuery()
            ->getSingleResult();
    }

    public function getOrganisationByCode( $code ){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('o')
            ->from(Organization::class, 'o')
            ->where('o.code = :code')
            ->setParameter('code', $code);
        return $qb->getQuery()->getSingleResult();
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
    public function getOrganizationByConnectorQuery( $connector, $value ){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('o')
            ->from(Organization::class, 'o')
            ->where('o.connectors LIKE :search')
            ->setParameter('search', '%"'.$connector.'";s:%:"'.$value.'";%');
        return $qb;
    }
}