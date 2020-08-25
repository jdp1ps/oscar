<?php
namespace Oscar\Connector;

use Doctrine\ORM\NoResultException;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Factory\JsonToOrganization;

class ConnectorOrganizationREST extends AbstractConnector implements IConnector
{
    private $editable = false;

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
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository(Organization::class);
    }

    protected function getServiceLocator(){
        return $this->getServicemanager();
    }


    public function execute( $force = false)
    {
        $personRepository = $this->getRepository();

        return $this->syncAll($personRepository, $force);
    }

    /**
     * @return JsonToOrganization
     */
    protected function factory(){
        static $factory;
        if( $factory === null )
            $factory = new JsonToOrganization();
        return $factory;
    }

    /**
     * @param OrganizationRepository $repository
     * @param bool $force
     * @return ConnectorRepport
     * @throws \Oscar\Exception\OscarException
     */
    function syncAll(OrganizationRepository $repository, $force)
    {
        $repport = new ConnectorRepport();

        $url = $this->getParameter('url_organizations');
        $repport->addnotice("URL : $url");

        /////////////////////////////////////
        ////// Patch 2.7 "Lewis" GIT#286 ////
        try {
            $access = $this->getAccessStrategy($url);
            $json = $access->getDatas();

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
                try {
                    /** @var Person $personOscar */
                    $organization = $repository->getObjectByConnectorID($this->getName(), $data->uid);
                    $action = "update";
                } catch( NoResultException $e ){
                    $organization = $repository->newPersistantObject();
                    $action = "add";
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

                    $repository->flush($organization);
                    if( $action == 'add' ){
                        $repport->addadded(sprintf("%s a été ajouté.", $organization->log()));
                    } else {
                        $repport->addupdated(sprintf("%s a été mis à jour.", $organization->log()));
                    }

                } else {
                    $repport->addnotice(sprintf("%s est à jour.", $organization->log()));
                }
            }
        } catch (\Exception $e ){
            $repport->adderror($e->getMessage());
        }


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

            $url = sprintf($this->getParameter('url_organization'), $organizationIdRemote);

            try {
                $access = $this->getAccessStrategy($url);
                $organizationData = $access->getDatas($organizationIdRemote);
                return $this->hydrateWithDatas($organization, $organizationData);
            } catch (\Exception $e) {
                throw new \Exception("Impossible de traiter des données : " . $e->getMessage());
            }
        } else {
            throw new \Exception('Impossible de synchroniser la structure ' . $organization);
        }
    }
}