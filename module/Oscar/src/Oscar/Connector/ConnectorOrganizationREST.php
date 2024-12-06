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

    private function errorOut(ConnectorRepport $repport, string $message):void {
        $repport->adderror($message);
        $this->getLogger()->error($message);
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

        try {
            $json = $this->getAccessStrategy()->getDataAll();
            $exist = $repository->getUidsConnector($this->getName());

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
                    $message = "L'organisation avec le code '$organisationId' n'est pas unique.";
                    $this->getLogger()->error($message);
                    $repport->adderror($message);
                    continue;
                } catch (\Exception $e) {
                    $this->errorOut($repport, "Problème inconnue avec '$organisationId' : ".$e->getMessage());
                    continue;
                }
                if( !property_exists($data, 'dateupdated') ){
                    $dateupdated = date('Y-m-d H:i:s');
                } else {
                    $dateupdated = $data->dateupdated;
                }

                if($organization->getDateUpdated() < new \DateTime($dateupdated) || $force == true ){
                    try {
                        $organization = $this->hydrateWithDatas($organization, $data);
                        if( property_exists($data, 'type') ){
                            try {
                                $type = $repository->getTypeObjByLabel($data->type);
                                $organization->setTypeObj($type);
                            } catch (\Exception $e){
                                $this->errorOut($repport, "Impossible de mettre à jour le type pour $organisationId, le type '".$data->type."' n'a pas été trouvédans Oscar : " . $e->getMessage());
                            }
                        }

                        $organization->setDateUpdated(new \DateTime($dateupdated));
                        $repository->flush($organization);
                    } catch (\Exception $e){
                        $this->errorOut($repport, "Impossible de mettre à jour les données de base de '$organisationId' : " . $e->getMessage());
                        continue;
                    }

                    if( $organization->hasUpdatedParentInCycle() ){

                        $this->getLogger()->debug("Mise à jour PARENT");
                        try {
                            $newParentCode = $organization->getUpdatedParentInCycle();
                            if( $newParentCode ){
                                $parent = null;
                                try {
                                    $parent = $this->getOrganizationService()
                                        ->getOrganizationRepository()
                                        ->getOrganisationByCode($newParentCode)
                                        ->getId();
                                } catch (\Exception $e) {
                                    $message = "Impossible d'ajouter le parent '$newParentCode' pour l'organisation '$organisationId', le parent n'existe pas dans OSCAR";
                                    $repport->addwarning($message);
                                    $this->getLogger()->warning("$message : " . $e->getMessage());
                                }
                                if( $parent ){
                                    try {
                                        $this->getOrganizationService()->saveSubStructure($parent, $organization->getId());
                                    }catch (\Exception $e) {
                                        $this->errorOut("Un problème est survenu lors de l'enregistrement du parent '$newParentCode' dans l'organisation $organisationId' : " . $e->getMessage());
                                        continue;
                                    }

                                }
                            } else {
                                try {
                                    $this->getOrganizationService()->removeSubStructure(null, $organization->getId());
                                } catch (\Exception $e) {
                                    $this->errorOut("Un problème est survenu lors de la suppression du parent '$newParentCode' dans l'organisation $organisationId' : " . $e->getMessage());
                                    continue;
                                }
                            }
                            $repository->flush($organization);
                        } catch (\Exception $e) {
                            $this->getLogger()->error("Erreur inconnue : " . $e->getMessage());
                        }
                    }

                    if( $action == 'add' ){
                        $message = "Organisation '$organisationId' ajoutée";
                        $this->getLogger()->info($message);
                        $repport->addadded($message);
                    } else {
                        $message = "Organisation '$organisationId' mise à jour";
                        $this->getLogger()->info($message);
                        $repport->addupdated($message);
                    }
                } else {
                    $repport->addnothing("Organisation '$organisationId' déjà à jour");
                }
            }

            $idsToDelete = [];

            foreach ($exist as $uid) {
                if (!$uid) {
                    continue;
                }
                try {
                    $organization = $repository->getObjectByConnectorID($this->getName(), $uid);

                    if ($this->getOptionPurge()) {
                        $idsToDelete[] = $organization->getId();
                    } else {
                        $repport->addnotice("'$uid' n'est plus dans le connecteur");
                    }
                } catch (\Exception $e) {
                    $this->errorOut("Problème avec l'organisation $uid : " . $e->getMessage());
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
            $this->errorOut("Erreur inattendue : " . $e->getMessage());
            throw $e;
        }

        $repport->addnotice("FIN du traitement...");
        $repport->addnotice("DUREE : " . $repport->getDuration() . " secondes");
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

    public function checkAccess()
    {
        parent::checkAccess();

        $json = $this->getAccessStrategy()->getDataAll();
        $jsonDatas = null;

        if(is_object($json) && property_exists($json, 'organizations') ){
            $jsonDatas = $json->organizations;
        } else {
            $jsonDatas = $json;
        }
        if (!is_array($jsonDatas)) {
            throw new \Exception("L'API n'a pas retourné un tableau de donnée");
        }

        echo " (" . \count($jsonDatas) . " organisations retournées par l'API) ";

        return true;
    }
}