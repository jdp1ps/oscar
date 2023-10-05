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
use Oscar\Exception\OscarException;
use Oscar\Factory\JsonToPersonFactory;

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
    protected function getOrganisationByCode($organisationCode)
    {
        return $this->getOrganizationRepository()->getOrganisationByCode($organisationCode);
    }

    /**
     * @param $organisationCode
     * @return mixed
     */
    protected function getOrganisationByConnectorId($uid)
    {
        return $this->getOrganizationRepository()->getOrganisationByCode($uid);
    }

    /**
     * @return array|null
     */
    protected function getRolesOscarByRoleId()
    {
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
    public function getRepport()
    {
        return $this->repport;
    }

    /**
     * @param $boolean
     * @return void
     */
    public function setPurge($boolean)
    {
        $this->purge = $boolean;
    }

    /**
     * @return mixed
     */
    public function getPurge()
    {
        return $this->purge;
    }

    /**
     * @return bool
     */
    public function isSuspect()
    {
        return $this->repport != null && $this->repport->isSuspect();
    }

    /**
     * @return mixed|JsonToPersonFactory
     */
    protected function factory()
    {
        static $factory;
        if ($factory === null) {
            $factory = new JsonToPersonFactory();
        }
        return $factory;
    }


    /**
     * @param Person $personOscar
     * @param $personData
     * @param $connectorName
     * @return Person
     * @throws OscarException
     * @throws \Doctrine\ORM\ORMException
     */
    public function hydratePerson(Person $personOscar, $personData, $connectorName)
    {
        $rolesOscar = $this->getRolesOscarByRoleId();
        $this->repport = new ConnectorRepport();

        try {
            $personOscar = $this->factory()->hydrateWithDatas($personOscar, $personData, $connectorName);
        } catch (OscarException $e) {
            $this->repport->adderror($e->getMessage());
            throw $e;
        }


        ///////////////////////////////////
        /// Récupération des rôles synchronisés par organisation
        $syncRoles = [];
        /** @var OrganizationPerson $organizationperson */
        foreach ($personOscar->getOrganizations() as $organizationperson) {
            if ($organizationperson->getOrigin() == $connectorName) {
                $organizationCode = $organizationperson->getOrganization()->getCode();
                if (!array_key_exists($organizationCode, $syncRoles)) {
                    $syncRoles[$organizationCode] = [];
                }
                if (!in_array($organizationperson->getRoleObj()->getRoleId(), $syncRoles[$organizationCode])) {
                    $syncRoles[$organizationCode][] = $organizationperson->getRoleObj()->getRoleId();
                }
            }
        }

        if (property_exists($personData, 'roles')) {
            var_dump($personData->roles);

            foreach ($personData->roles as $organizationCode => $roles) {
                try {
                    /** @var Organization $organization */
                    $organization = $this->getOrganisationByCode($organizationCode);

                    if ($organization) {
                        foreach ($roles as $roleId) {
                            if (array_key_exists($organizationCode, $syncRoles)) {
                                if (in_array($roleId, $syncRoles[$organizationCode])) {
                                    array_splice(
                                        $syncRoles[$organizationCode],
                                        array_search($roleId, $syncRoles[$organizationCode]),
                                        1
                                    );
                                }
                            }
                            if (array_key_exists($roleId, $rolesOscar)) {
                                if (!$organization->hasPerson($personOscar, $roleId)) {
                                    $roleOscar = new OrganizationPerson();
                                    $this->entityManager->persist($roleOscar);
                                    $roleOscar->setPerson($personOscar)
                                        ->setOrganization($organization)
                                        ->setOrigin($connectorName)
                                        ->setRoleObj($rolesOscar[$roleId]);
                                    $personOscar->getOrganizations()->add($roleOscar);
                                    $this->repport->addupdated(
                                        sprintf(
                                            "Ajout du rôle '%s' dans '%s' pour '%s' ",
                                            $roleId,
                                            $organization,
                                            $personOscar
                                        )
                                    );
                                }
                            } else {
                                $this->repport->addwarning(
                                    sprintf(
                                        "Le role '%s' n'a pas été ajouté à '%s' dans '%s' car il est absent de Oscar",
                                        $roleId,
                                        $personOscar,
                                        $organization
                                    )
                                );
                            }
                        }
                    } else {
                        $this->repport->addwarning(
                            sprintf(
                                "L'organisation avec le code '%s' n'existe pas dans Oscar",
                                $organizationCode
                            )
                        );
                    }
                } catch (NoResultException $e) {
                    $this->repport->addwarning(
                        sprintf(
                            "Impossible de charger l'organisation avec le code '%s'.",
                            $organizationCode
                        )
                    );
                } catch (NonUniqueResultException $e) {
                    $this->repport->addwarning(
                        sprintf(
                            "L'organisation avec le code '%s' est présente plusieurs fois.",
                            $organizationCode
                        )
                    );
                }
            }
        }

        // Purge des rôles supprimés
        foreach ($syncRoles as $code => $roles) {
            if (count($syncRoles[$code]) < 0) {
                continue;
            }

            /** @var OrganizationPerson $organizationPerson */
            foreach ($personOscar->getOrganizations() as $organizationPerson) {
                $roleId = $organizationPerson->getRole();
                $codeOrg = $organizationPerson->getOrganization()->getCode();
                if ($codeOrg == $code) {
                    if (in_array($roleId, $syncRoles[$code])) {
                        if ($this->getPurge()) {
                            $this->entityManager->remove($organizationPerson);
                            $this->repport->addremoved(
                                sprintf(
                                    "Suppression du rôle %s pour %s dans %s.",
                                    $roleId,
                                    $personOscar,
                                    $organizationPerson->getOrganization()
                                )
                            );
                        } else {
                            $this->repport->addwarning(
                                sprintf(
                                    "Suppression du rôle %s pour %s dans %s dans la source (activer la purge pour le supprimer).",
                                    $roleId,
                                    $personOscar,
                                    $organizationPerson->getOrganization()
                                )
                            );
                        }
                    }
                }
            }
        }
        return $personOscar;
    }
}