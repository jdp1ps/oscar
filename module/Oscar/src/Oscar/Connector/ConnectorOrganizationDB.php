<?php

namespace Oscar\Connector;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Exception\OscarException;
use Oscar\Factory\JsonToOrganization;
use Oscar\Service\OrganizationService;

class ConnectorOrganizationDB extends AbstractConnector
{

    /**
     * @param bool $force
     * @return ConnectorRepport
     * @throws OscarException
     */
    public function execute( bool $force = false) :ConnectorRepport
    {
        $personRepository = $this->getRepository();
        return $this->syncAll($personRepository, $force);
    }

    /**
     * @param OrganizationRepository $repository
     * @param bool $force
     * @return ConnectorRepport
     * @throws OscarException
     */
    function syncAll(OrganizationRepository $repository, bool $force = false)
    {
        $this->getLogger()->info("Synchronisation des structures");
        $report = new ConnectorRepport();

        try {
            $rows = $this->getAccessStrategy()->getDataAll($this->mapParams());

            $orgs = [];
            foreach( $rows as $row ){
                $orgs[] = $this->objectFromDBRow($row);
            }

            $exist = $repository->getUidsConnector($this->getName());
            $jsonDatas = null;

            if( is_object($orgs) && property_exists($orgs, 'organizations') ){
                $jsonDatas = $orgs->organizations;
            } else {
                $jsonDatas = $orgs;
            }

            if( !is_array($jsonDatas) ){
                throw new \Exception("L'API n'a pas retourné un tableau de donnée");
            }
            ////////////////////////////////////

            foreach( $jsonDatas as $data ){
                $organisationId = $data->uid;

                if (($index = array_search($organisationId, $exist)) >= 0) {
                    array_splice($exist, $index, 1);
                }

                try {
                    $organization = $repository->getObjectByConnectorID($this->getName(), $organisationId);
                    $action = "update";
                } catch( NoResultException $e ){
                    $organization = $repository->newPersistantObject();
                    $action = "add";
                } catch (NonUniqueResultException $e){
                    $this->getLogger()->error("L'organisation avec le code '$organisationId' n'est pas unique.");
                }
                if( !property_exists($data, 'dateupdated') ){
                    $dateupdated = date('Y-m-d H:i:s');
                } else {
                    $dateupdated = $data->dateupdated;
                }

                if($organization->getDateUpdated() < new \DateTime($dateupdated) || $force == true ){

                    $organization = $this->hydrateWithDatas($organization, $data);
                    if( property_exists($data, 'type') )
                        $organization->setTypeObj($repository->getTypeObjByLabel($data->type));

                    $organization->setDateUpdated(new \DateTime($dateupdated));
                    $repository->flush($organization);

                    if( $organization->hasUpdatedParentInCycle() ){
                        try {
                            $newParentCode = $organization->getUpdatedParentInCycle();
                            if( $newParentCode ){
                                $parent = $this->getOrganizationService()->getOrganizationRepository()->getOrganisationByCode($newParentCode)->getId();
                                $this->getOrganizationService()->saveSubStructure($parent, $organization->getId());
                            } else {
                                $this->getOrganizationService()->removeSubStructure(null, $organization->getId());
                            }
                            $repository->flush($organization);
                        } catch (\Exception $e) {
                            $this->getLogger()->error("Erreur : " . $e->getMessage());
                        }
                    }

                    if( $action == 'add' ){
                        $this->getLogger()->info("Organisation '$organisationId' ajoutée");
                    } else {
                        $this->getLogger()->info("Organisation '$organisationId' mise à jour");
                    }
                }
            }

            $idsToDelete = [];

            foreach ($exist as $uid) {
                if (!$uid) {
                    continue;
                }
                try {
                    $organization = $repository->getObjectByConnectorID($this->getName(), $uid);
                    $this->getLogger()->info("'$organization' n'est plus présent dans les données du connecteur");

                    if ($this->getOptionPurge()) {
                        $idsToDelete[] = $organization->getId();
                    }
                } catch (\Exception $e) {
                    $this->getLogger()->error($e->getMessage());
                }
            }
            foreach ($idsToDelete as $id) {
                try {
                    $repository->removeOrganizationById($id);
                    $this->getLogger()->error("Supression de l'organisation '$id'");
                } catch (\Exception $e) {
                    $this->getLogger()->error("Impossible de supprimer l'organisation : " . $e->getMessage());
                    throw $e;
                }
            }
        } catch (\Exception $e ){
            $report->adderror($e->getMessage());
            throw $e;
        }

        $report->addnotice("FIN du traitement...");
        return $report;
    }

    private function mapParams() {
        $dbParam = [];
        $dbParam['db_host'] = $this->getParameter('db_host');
        $dbParam['db_port'] = $this->getParameter('db_port');
        $dbParam['db_user'] = $this->getParameter('db_user');
        $dbParam['db_password'] = $this->getParameter('db_password');
        $dbParam['db_name'] = $this->getParameter('db_name');
        $dbParam['db_query_all'] = $this->getParameter('db_query_all');
        return $dbParam;
    }

    private function objectFromDBRow($row) {
        $org = new \stdClass();
        $org->uid = $row['ID'];
        $org->code = $row['CODE'];
        $org->shortname = $row['LIBELLE_COURT'];
        $org->dateupdate = $row['UPDATED_AT'];
        $org->dateupdated = $row['UPDATED_AT'];
        $org->labintel = NULL;
        if ($row['TYPE_RECHERCHE'] != NULL && $row['CODE_RECHERCHE'] != NULL) {
            $org->labintel = $row['TYPE_RECHERCHE'] . $row['CODE_RECHERCHE'];
        }
        $org->longname = $row['LIBELLE_LONG'];
        $org->phone = $row['TELEPHONE'];
        $org->description = NULL;
        $org->email = NULL;
        $org->url = $row['SITE_URL'];
        $org->siret = NULL;
        $org->type = $row['TYPE'];
        $org->duns = NULL;
        $org->tvaintra = NULL;
        $org->rnsr = $row['RNSR'];
        $org->parent = $row['PARENT'];

        $addr_json = $row['ADRESSE_POSTALE'];
        $addr = NULL;
        if ($addr_json != NULL) {
            $addr = json_decode($addr_json);
        }
        $org->address = $addr;

        return $org;
    }

    private function hydrateWithDatas( Organization $organization, $data ){
        return $this->factory()->hydrateWithDatas($organization, $data, $this->getName());
    }

    /**
     * @return JsonToOrganization
     */
    protected function factory() :JsonToOrganization
    {
        static $factory;
        if( $factory === null ) {
            $types = $this->getRepository()->getTypesKeyLabel();
            $factory = new JsonToOrganization($types);
        }
        return $factory;
    }

    /**
     * @return OrganizationRepository
     */
    public function getRepository()
    {
        return $this->getServiceLocator()->get(EntityManager::class)->getRepository(Organization::class);
    }

    function getRemoteID()
    {
        return "code";
    }
    
    function getRemoteFieldname($oscarFieldName)
    {

    }

    public function getPathAll(): string
    {
        return $this->getParameter('url_organizations');
    }

    public function getPathSingle($remoteId): string
    {
        return sprintf($this->getParameter('url_organization'), $remoteId);
    }

    /**
     * @return OrganizationService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getOrganizationService()
    {
        return $this->getServiceLocator()->get(OrganizationService::class);
    }

    public function logError($msg) {
        $this->getLogger()->error($msg);
    }

    public function checkAccess()
    {
        parent::checkAccess();

        $rows = $this->getAccessStrategy()->getDataAll($this->mapParams());
        if (!is_array($rows)) {
            throw new \Exception("Le connecteur OrganizationDB n'a pas retourné un tableau de donnée");
        }
        echo " (" . \count($rows) . " organisations trouvées en DB) ";
        return true;
    }
}
