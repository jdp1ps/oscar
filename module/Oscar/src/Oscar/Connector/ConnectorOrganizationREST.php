<?php
namespace Oscar\Connector;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Connector\Access\ConnectorAccessCurlHttp;
use Oscar\Connector\DataAccessStrategy\HttpBasicStrategy;
use Oscar\Connector\DataAccessStrategy\IDataAccessStrategy;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Exception\OscarException;
use Oscar\Factory\JsonToOrganization;
use Oscar\Service\OrganizationService;

class ConnectorOrganizationREST extends AbstractConnector
{
    private bool $editable = false;

    public function setEditable($editable){
        $this->editable = $editable;
    }

    public function isEditable(){
        return $this->editable;
    }

    function getRemoteID()
    {
        return "code";
    }

    function getRemoteFieldname($oscarFieldName)
    {
        // TODO: Implement getRemoteFieldname() method.
    }

    function getPersonData($idConnector)
    {
        // TODO: Implement getPersonData() method.
    }

    public function getConfigData()
    {
        return null;
    }

    /**
     * @return OrganizationRepository
     */
    public function getRepository()
    {
        return $this->getServiceLocator()->get(EntityManager::class)->getRepository(Organization::class);
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

    protected function getServiceLocator(){
        return $this->getServicemanager();
    }

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

    protected function getLogger()
    {
        return $this->getOrganizationService()->getLoggerService();
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
        $repport = new ConnectorRepport();

        $url = $this->getParameter('url_organizations');
        $repport->addnotice("URL : $url");

        /////////////////////////////////////
        ////// Patch 2.7 "Lewis" GIT#286 ////
        try {
            $json = $this->getAccessStrategy()->getDataAll();
            $exist = $repository->getUidsConnector($this->getName());
            $jsonDatas = null;

            if( is_object($json) && property_exists($json, 'organizations') ){
                $jsonDatas = $json->organizations;
            } else {
                $jsonDatas = $json;
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
                    /** @var Person $personOscar */
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
            $repport->adderror($e->getMessage());
            throw $e;
        }
        die();


        $repport->addnotice("FIN du traitement...");
        return $repport;
    }

    private function hydrateWithDatas( Organization $organization, $data ){
        return $this->factory()->hydrateWithDatas($organization, $data, $this->getName());
    }

    function syncOrganization(Organization $organization)
    {
        if ($organization->getConnectorID($this->getName())) {
            $organizationIdRemote = $organization->getConnectorID($this->getName());
            try {
                $organizationData = $this->getAccessStrategy()->getDataSingle($organizationIdRemote);
                if( property_exists($organizationData, 'person') ){
                    $organizationData = $organizationData->person;
                }
                if( property_exists($organizationData, 'organization') ){
                    $organizationData = $organizationData->organization;
                }
                return $this->hydrateWithDatas($organization, $organizationData);
            } catch (\Exception $e) {
                throw new \Exception("Impossible de traiter des données : " . $e->getMessage());
            }
        } else {
            throw new \Exception('Impossible de synchroniser la structure ' . $organization);
        }
    }

    public function getPathAll(): string
    {
        return $this->getParameter('url_organizations');
    }

    public function getPathSingle($remoteId): string
    {
        return sprintf($this->getParameter('url_organization'), $remoteId);
    }
}