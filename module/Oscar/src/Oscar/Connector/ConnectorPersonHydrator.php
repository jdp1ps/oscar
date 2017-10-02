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
use Oscar\Exception\ConnectorException;

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

        ////////////////////////////////////////////////////////////////////////
        ///
        /// Champs obligatoires
        if( !property_exists($personData, 'uid') ){
            $this->repport->adderror("Le champ 'uid' est obligatoire.");
            return;
        }

        if( !property_exists($personData, 'firstname') ){
            $this->repport->adderror("Le champ 'firstname' est manquant pour l'entrée UID = " . $personData->uid);
            return;
        }

        if( !property_exists($personData, 'lastname') ){
            $this->repport->adderror("Le champ 'lastname' est manquant pour l'entrée UID = " . $personData->uid);
            return;
        }

        ///
        /// Champs facultatifs
        if( property_exists($personData, 'login') ) $personOscar->setLadapLogin($personData->login);
        if( property_exists($personData, 'groups') ) $personOscar->setLdapMemberOf($personData->groups);
        if( property_exists($personData, 'structure') ) $personOscar->setLdapSiteLocation($personData->structure);
        if( property_exists($personData, 'affectation') ) $personOscar->setLdapAffectation($personData->affectation);
        if( property_exists($personData, 'status') ) $personOscar->setLdapStatus($personData->status);
        if( property_exists($personData, 'phone') ) $personOscar->setPhone($personData->phone);
        if( property_exists($personData, 'inm') ) $personOscar->setHarpegeINM($personData->inm);
        if( property_exists($personData, 'mail') ) $personOscar->setEmail($personData->mail);



        $personOscar->setConnectorID($connectorName, $personData->uid)
            ->setFirstname($personData->firstname)
            ->setLastname($personData->lastname)
            ->setDateSyncLdap(new \DateTime())
            ;

        if( property_exists($personData, 'roles') ) {
            foreach ($personData->roles as $organizationCode => $roles) {

                try {
                    /** @var Organization $organization */
                    $organization = $this->getOrganisationByCode($organizationCode);

                    if ($organization) {
                        foreach ($roles as $roleId) {
                            if (array_key_exists($roleId, $rolesOscar)) {
                                if (!$organization->hasPerson($personOscar,
                                    $roleId)) {
                                    $roleOscar = new OrganizationPerson();
                                    $this->entityManager->persist($roleOscar);
                                    $roleOscar->setPerson($personOscar)
                                        ->setOrganization($organization)
                                        ->setRoleObj($rolesOscar[$roleId]);
                                    $personOscar->getOrganizations()->add($roleOscar);
                                    $this->repport->addupdated(sprintf("%s a le role '%s' dans %s", $personOscar, $roleId, $organization));
                                }
                            } else {
                                $this->repport->addwarning(sprintf("Le role '%s' n'a pas été ajouté à '%s' dans '%s' car il est absent de Oscar", $roleId, $personOscar, $organization));
                            }
                        }
                    } else {
                        $this->repport->addwarning(sprintf("L'organisation avec le code '%s' n'existe pas dans Oscar", $organizationCode));
                    }
                } catch (NoResultException $e) {
                    $this->repport->addwarning(sprintf("Impossible de charger l'organisation avec le code '%s'.",
                        $organizationCode));
                } catch (NonUniqueResultException $e) {
                    $this->repport->addwarning(sprintf("L'organisation avec le code '%s' est présente plusieurs fois.",
                        $organizationCode));
                }
            }
        }

        return $personOscar;
    }



}