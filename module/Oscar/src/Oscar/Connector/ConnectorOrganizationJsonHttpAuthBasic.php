<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/01/20
 * Time: 10:52
 */

namespace Oscar\Connector;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Connector\DataAccessStrategy\HttpAuthBasicStrategy;
use Oscar\Connector\DataAccessStrategy\IDataAccessStrategy;
use Oscar\Connector\DataExtractionStrategy\DataExtractionStringToJsonStrategy;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Factory\JsonToOrganization;

class ConnectorOrganizationJsonHttpAuthBasic extends AbstractConnectorOscar
{
    private $organizationHydrator;


    /**
     * @return OrganizationRepository
     */
    public function getOrganizationRepository(){
        return $this->getEntityManager()->getRepository(Organization::class);
    }

    public function getEntityManager(){
        return $this->getServiceManager()->get(EntityManager::class);
    }

    function execute($force = true)
    {
        $dataAccessStrategy         = new HttpAuthBasicStrategy($this);
        $dataExtractionStrategy     = new DataExtractionStringToJsonStrategy();
        $report                     = new ConnectorRepport();

        // Récupération des données
        try {
            $datas = $dataAccessStrategy->getDataAll();
        } catch (\Exception $e) {
            throw new \Exception("Impossible de charger des données depuis $url  : " . $e->getMessage());
        }

        // Conversion
        try {
            $json = $dataExtractionStrategy->extract($datas);
            // Autorise la présence d'une clef 'persons' au premier niveau (facultatif)
            if( is_object($json) && property_exists($json, 'organizations') ){
                $organizationsDatas = $json->organizations;
            } else {
                $organizationsDatas = $json;
            }
        } catch (\Exception $e) {
            $report->adderror("Data get all error : " . $e->getMessage());
            throw new \Exception("Impossible de convertir les données : " . $e->getMessage());
        }

        if( !is_array($organizationsDatas) ){
            throw new \Exception("L'API n'a pas retourné un tableau de donnée");
        }

        // ...
        $this->syncAll($organizationsDatas, $this->getOrganizationRepository(), $report, $this->getOption('force', false));

        return $report;
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
    function syncAll($organizationsDatas, OrganizationRepository $repository, ConnectorRepport $repport, $force)
    {


        /////////////////////////////////////
        ////// Patch 2.7 "Lewis" GIT#286 ////
        try {

            foreach( $organizationsDatas as $data ){
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
                    if( $data->dateupdated == null )
                        $data->dateupdated = "";
                    else
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
            try {
                $json = $this->getDataAccess()->getDataSingle($organization->getConnectorID($this->getName()));
                if( is_object($json) && property_exists($json, 'organization') ){
                    $organizationData = $json->organization;
                } else {
                    $organizationData = $json;
                }
                return $this->hydrateWithDatas($organization, $organizationData);
            } catch (\Exception $e) {
                throw new \Exception("Impossible de traiter des données : " . $e->getMessage());
            }
        } else {
            throw new \Exception('Impossible de synchroniser la structure ' . $organization);
        }
    }

    public function getDataAccess(): IDataAccessStrategy
    {
        return new HttpAuthBasicStrategy($this);
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