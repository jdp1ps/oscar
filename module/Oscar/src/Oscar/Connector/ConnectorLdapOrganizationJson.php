<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/01/20
 * Time: 10:52
 */

namespace Oscar\Connector;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Oscar\Connector\DataAccessStrategy\HttpAuthBasicStrategy;
use Oscar\Connector\DataAccessStrategy\IDataAccessStrategy;
use Oscar\Connector\DataExtractionStrategy\DataExtractionStringToJsonStrategy;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationLdap;
use Oscar\Entity\OrganizationRepository;
use Oscar\Factory\JsonToOrganization;
use Zend\Ldap\Ldap;

class ConnectorLdapOrganizationJson extends AbstractConnectorOscar
{
    private $organizationHydrator;
    private $editable;
    private $connectorID;

    const LDAP_FILTER_ALL = '*';
    private $configLdap = array(
        "type" => "organization_ldap",
        "label" => "Organization Ldap",
        "filtrage" => "&(objectClass=supannEntite)(supannTypeEntite={SUPANN}S*)(businessCategory=research),&(objectClass=supannEntite)(supannTypeEntite={SUPANN}S*)(businessCategory=administration)"
        //"filtrage" => "DIREVAL"
    );

    private $configPath = "/var/www/html/oscar/config/autoload/../connectors/organization_ldap.yml";
    private $configFile;

    public function getConfigData()
    {
        return $this->configLdap;
    }

    public function setConfigData($config)
    {
        $this->configLdap = $config;
    }

    public function getConfigFile()
    {
        return $this->configFile;
    }

    public function setConfigFile($file)
    {
        $this->configFile = $file;
    }

    public function setEditable($editable){
        $this->editable = $editable;
    }

    public function getEditable(){
        return $this->editable;
    }

    public function setConnectorId($connectorId){
        $this->connectorID = $connectorId;
    }

    public function getConnectorId(){
        return $this->connectorID;
    }

    public function updateParameters($config)
    {
        $dataConfig = $config;
        if(!is_array($config))
            $dataConfig = $config->toArray();

        $this->configLdap["filtrage"] = $dataConfig["filtrage"];
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
        //$dataAccessStrategy         = new HttpAuthBasicStrategy($this);
        $dataExtractionStrategy     = new DataExtractionStringToJsonStrategy();
        $this->setConnectorId('organization_ldap');
        $moduleOptions = $this->getServicemanager()->get('unicaen-app_module_options');

        $this->configFile = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPath));

        if($this->configLdap["filtrage"] == null){
            $configFiltre["filtrage"] = $this->configFile['filtre_ldap'];
            $this->updateParameters($configFiltre);
        }

        $configLdap = $moduleOptions->getLdap();
        $ldap = $configLdap['connection']['default']['params'];

        $dataStructureFromLdap = new OrganizationLdap();
        $dataStructureFromLdap->setConfig($configLdap);

        $dataStructureFromLdap->setLdap(new Ldap($ldap));

        $report = new ConnectorRepport();

        // Récupération des données
        try {
            //$data = $dataAccessStrategy->getDataAll();
            $filtrage = $this->configLdap["filtrage"];
            $dataFiltrage = explode(",", $filtrage);
            $data = array();

            foreach($dataFiltrage as $filtre) {
                $dataOrg = null;
                $dataOrg = $dataStructureFromLdap->findOneByFilter($filtre);

                //$dataOrg = $dataStructureFromLdap->findOneByDn($filtre);

                foreach($dataOrg as $organization){
                    $dataProcess = array();
                    $dataProcess['uid'] = $organization["supannrefid"];
                    $dataProcess['name'] = $organization["description"];
                    $dataProcess['dateupdate'] = null;
                    $dataProcess['code'] = $organization["supanncodeentite"];
                    $dataProcess['labintel'] = "";
                    $dataProcess['shortname'] = $organization["ou"];
                    $dataProcess['longname'] = $organization["description"];
                    $dataProcess['phone'] = $organization["telephonenumber"];
                    $dataProcess['description'] = $organization["description"];
                    $dataProcess['email'] = "";
                    $dataProcess['siret'] = "";
                    $dataProcess['type'] = $organization["supanntypeentite"];
                    $dataProcess['url'] = $organization["labeleduri"];
                    $dataProcess['duns'] = $organization["telephonenumber"];
                    $dataProcess['tvaintra'] = null;
                    $dataProcess['rnsr'] = null;

                    $address = explode("$",$organization["postaladdress"]);
                    $dataProcess['address'] = array(
                        "address1" => $address[0],
                        "address2" => $address[1],
                        "zipcode" => $address[2],
                        "city" => $address[3],
                        "country" => $address[4],
                        "address3" => ""
                    );

                    $data[] = (object) $dataProcess;
                }
            }

        } catch (\Exception $e) {
            throw new \Exception("Impossible de charger des données depuis  : " . $e->getMessage());
        }

        // Conversion
        /*try {
            $json = $dataExtractionStrategy->extract($data);
            // Autorise la présence d'une clef 'persons' au premier niveau (facultatif)
            if( is_object($json) && property_exists($json, 'organizations') ){
                $organizationsData = $json->organizations;
            } else {
                $organizationsData = $json;
            }
        } catch (\Exception $e) {
            $report->adderror("Data get all error : " . $e->getMessage());
            throw new \Exception("Impossible de convertir les données : " . $e->getMessage());
        }*/

        if( !is_array($data) ){
            throw new \Exception("LDAP n'a pas retourné un tableau de donnée");
        }

        // ...
        $this->syncAll($data, $this->getOrganizationRepository(), $report, $this->getOption('force', false));

        return $report;
    }

    /**
     * @return JsonToOrganization
     */
    protected function factory(){
        static $factory;
        if( $factory === null ) {
            $types = $this->getRepository()->getTypesKeyLabel();
            $factory = new JsonToOrganization($types);
        }
        return $factory;
    }

    /**
     * @param OrganizationRepository $repository
     * @param bool $force
     * @return ConnectorRepport
     * @throws \Oscar\Exception\OscarException
     */
    function syncAll($organizationsData, OrganizationRepository $repository, ConnectorRepport $report, $force)
    {
        try {

            foreach( $organizationsData as $data ){
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
                        $organization->setTypeObj($repository->getTypeObjByLabel($data->name));

                    $repository->flush($organization);
                    if( $action == 'add' ){
                        $report->addadded(sprintf("%s a été ajouté.", $organization->log()));
                    } else {
                        $report->addupdated(sprintf("%s a été mis à jour.", $organization->log()));
                    }

                } else {
                    $report->addnotice(sprintf("%s est à jour.", $organization->log()));
                }
            }
        } catch (\Exception $e ){
            $report->adderror($e->getMessage());
        }


        $report->addnotice("FIN du traitement...");
        return $report;
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