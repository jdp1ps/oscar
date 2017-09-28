<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-28 17:07
 * @copyright Certic (c) 2017
 */

namespace Oscar\Connector;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\Entity\RoleRepository;

/**
 * Cette classe centralise l'analyse des données d'un personne pour mettre à jour un objet Person avec.
 *
 * Class ConnectorPersonHydrator
 * @package Oscar\Connector
 */
class ConnectorPersonHydrator
{
    /**
     * @return OrganizationRepository
     */
    public function getOrganizationRepository()
    {
        return $this->entityManager->getRepository(Organization::class);
    }

    /**
     * @return RoleRepository
     */
    public function getRoleRepository()
    {
        return $this->entityManager->getRepository(Role::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getOrganizationPersonRepository()
    {
        return $this->entityManager->getRepository(OrganizationPerson::class);
    }

    private $repport;


    /**
     * @param $organisationCode
     * @return mixed
     */
    protected function getOrganisationByCode( $organisationCode )
    {
        return $this->getOrganizationRepository()->getOrganisationByCode($organisationCode);
    }

    /**
     * @return array|null
     */
    protected function getRolesOscarByRoleId(){
        return $this->getRoleRepository()->getRolesOscarByRoleId();
    }

    /**
     * ConnectorPersonHydrator constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return mixed
     */
    public function getRepport(){
        return $this->repport;
    }

    /**
     * @return bool
     */
    public function isSuspect(){
        return $this->repport != null && $this->repport->isSuspect();
    }


    public function hydratePerson( Person $personOscar, $personData, $connectorName )
    {
        $rolesOscar = $this->getRolesOscarByRoleId();
        $this->repport = new ConnectorRepport();

        foreach( $personData->roles as $organizationCode=>$roles ){

            try {
                /** @var Organization $organization */
                $organization = $this->getOrganisationByCode($organizationCode);

                if( $organization ){
                    foreach( $roles as $roleId ){
                        if( array_key_exists($roleId, $rolesOscar) ){
                            if( !$organization->hasPerson($personOscar, $roleId) ){
                                $roleOscar = new OrganizationPerson();
                                $this->entityManager->persist($roleOscar);
                                $roleOscar->setPerson($personOscar)
                                    ->setOrganization($organization)
                                    ->setRoleObj($rolesOscar[$roleId]);
                                $personOscar->getOrganizations()->add($roleOscar);
                            }
                        }
                    }
                }
            } catch (NoResultException $e){
                $this->repport->addwarning(sprintf("Impossible de charger l'organisation avec le code %s.", $organizationCode));
            } catch ( NonUniqueResultException $e ){
                $this->repport->addwarning(sprintf("L'organisation avec le code %s est présente plusieurs fois.", $organizationCode));
            }
        }

        return $personOscar->setConnectorID($connectorName, $personData->uid)
            ->setLadapLogin($personData->login)
            ->setFirstname($personData->firstname)
            ->setLastname($personData->lastname)
            ->setEmail($personData->mail)
            ->setHarpegeINM($personData->inm)
            ->setPhone($personData->phone)
            ->setDateSyncLdap(new \DateTime())
            ->setLdapStatus($personData->status)
            ->setLdapAffectation($personData->affectation)
            ->setLdapSiteLocation($personData->structure)
            ->setLdapMemberOf($personData->groups);

        return $person;
    }



}