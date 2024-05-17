<?php
/**
 * @author Joachim Dornbusch<joachim.dornbusch@univ-paris1.fr>
 * @date: 2024-05-17
 */

namespace Oscar\Connector;

use Doctrine\ORM\NoResultException;
use Oscar\Connector\Access\ConnectorAccessLdap;
use Oscar\Connector\Access\IConnectorAccess;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Factory\LdapToOrganization;
use function PHPUnit\Framework\isEmpty;

class ConnectorOrganizationLDAP extends ConnectorOrganizationREST
{

    /**
     * @return LdapToOrganization
     */
    protected function factory()
    {
        static $factory;
        if ($factory === null) {
            $types = $this->getRepository()->getTypesKeyLabel();
            $typeMappings = $this->getParameter("organisation_types");
            $factory = new LdapToOrganization($types, $typeMappings);
        }
        return $factory;
    }


    /**
     * @param OrganizationRepository $repository
     * @param bool $force
     * @return ConnectorRepport
     * @throws \Oscar\Exception\OscarException
     */
    public function syncAll(OrganizationRepository $repository, $force)
    {
        $repport = new ConnectorRepport();
        try {
            $ldapData = $this->getAccessStrategy()->getDataAll();

            if (!is_array($ldapData)) {
                throw new \Exception("L'annuaire LDAP n'a pas retourné un tableau de donnée");
            }

            foreach ($ldapData as $data) {
                try {
                    /** @var Organization $organization */
                    $organization = $repository->getObjectByConnectorID($this->getName(), $data->supanncodeentite);
                    $action = "update";
                } catch (NoResultException $e) {
                    $organization = $repository->newPersistantObject();
                    $action = "add";
                }
                if (property_exists($data, 'modifytimestamp')) {
                    $rawdateupdated = $data->modifytimestamp;
                    $dateupdated = \DateTime::createFromFormat('YmdHis',
                        substr($rawdateupdated, 0, 14),
                        new \DateTimeZone('UTC')
                    );
                }
                if (!isset($dateupdated) || !$dateupdated instanceof \DateTime) {
                    $dateupdated = new \DateTime();
                }

                if ($organization->getDateUpdated() < $dateupdated || $force) {
                    try {
                        $organization = $this->syncOrganization($organization);
                        $organization = $this->hydrateWithDatas($organization, $data);

                        $repository->flush($organization);
                        if ($action == 'add') {
                            $repport->addadded(sprintf("%s a été ajouté.", $organization->log()));
                        } else {
                            $repport->addupdated(sprintf("%s a été mis à jour.", $organization->log()));
                        }
                    } catch (\Exception $e) {
                        $repport->adderror(sprintf("Erreur lors de la synchronisation de %s : %s",
                            $organization->log(), $e->getMessage()));
                    }


                } else {
                    $repport->addnotice(sprintf("%s est à jour.", $organization->log()));
                }
            }
        } catch (\Exception $e) {
            $repport->adderror($e->getMessage());
            throw $e;
        }


        $repport->addnotice("FIN du traitement...");
        return $repport;
    }

    /**
     * @return IConnectorAccess
     */
    protected function getAccessStrategy()
    {
        $accessStrategy = ConnectorAccessLdap::class;
        $access = new $accessStrategy($this);
        $access->setOptions($this->getServicemanager()->get('unicaen-app_module_options'));
        return $access;
    }

    public function getFilters()
    {
        return $this->getParameter('organisation_ldap_filters');
    }

    private function hydrateWithDatas(Organization $organization, $data)
    {
        return $this->factory()->hydrateWithDatas($organization, $data, $this->getName());
    }

    public function syncOrganization(Organization $organization)
    {
        if ($organization->getConnectorID($this->getName())) {
            $organizationIdRemote = $organization->getConnectorID($this->getName());
            try {
                $organizationsData = $this->getAccessStrategy()->getDataSingle($organizationIdRemote);
                if (count($organizationsData) > 1) {
                    throw new \Exception(
                        "L'annuaire LDAP a retourné plusieurs organisations pour l'identifiant " . $organizationIdRemote
                    );
                }
                if (empty($organizationsData)) {
                    throw new \Exception(
                        "L'annuaire LDAP n'a pas retourné d'organisation pour l'identifiant " . $organizationIdRemote
                    );
                }
                $organizationData = $organizationsData[0];
                return $this->hydrateWithDatas($organization, $organizationData);
            } catch (\Exception $e) {
                throw new \Exception("Impossible de traiter des données : " . $e->getMessage());
            }
        } else {
            throw new \Exception('Impossible de synchroniser la structure ' . $organization);
        }
    }
}
