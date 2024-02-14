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
use Oscar\Exception\OscarException;
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

    private $arrayTypes = array(
        "association",
        "collectivite",
        "composante",
        "etablissement",
        "groupement",
        "inconnue",
        "institution",
        "laboratoire",
        "plateau",
        "societe",
    );

    private $configPath = null;
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
        $this->setConnectorId('organization_ldap');
        $moduleOptions = $this->getServicemanager()->get('unicaen-app_module_options');
        $this->configPath = realpath(__DIR__.'/../../') . "/../../../config/connectors/organization_ldap.yml";

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
            $filtrage = $this->configLdap["filtrage"];
            $dataFiltrage = explode(",", $filtrage);
            $data = array();

            foreach($dataFiltrage as $filtre) {
                $dataOrg = null;
                $dataOrg = $dataStructureFromLdap->findOneByFilter($filtre);

                foreach($dataOrg as $organization){
                    $dataProcess = array();

                    $dataProcess['uid'] = $organization["supannrefid"];
                    $dataProcess['name'] = $organization["description"];
                    $dataProcess['dateupdate'] = null;
                    $dataProcess['code'] = $organization["supanncodeentite"];
                    $dataProcess['shortname'] = $organization["ou"];
                    $dataProcess['longname'] = $organization["description"];
                    $dataProcess['phone'] = isset($organization["telephonenumber"]) ? $organization["telephonenumber"] : null;
                    $dataProcess['description'] = $organization["description"];
                    $dataProcess['email'] = "";
                    $dataProcess['siret'] = "";
                    $dataProcess['url'] = isset($organization["labeleduri"]) ? $organization["labeleduri"] : null;
                    $dataProcess['duns'] = null;
                    $dataProcess['tvaintra'] = null;

                    $dataProcess['rnsr'] = "";
                    $dataProcess['labintel'] = "";

                    if(isset($organization["supanntypeentite"])){
                        $dataProcess['type'] = $this->verifyTypes($organization["supanntypeentite"]);
                    }

                    if(is_array($organization["supannrefid"])){
                        foreach($organization["supannrefid"] as $refId){
                            if(str_contains($refId, 'CNRS')){
                                $dataProcess['labintel'] = $refId;
                            }

                            if(str_contains($refId, 'RNSR')){
                                $dataProcess['rnsr'] = $refId;
                            }
                        }

                    } else {
                        if(isset($organization["supannrefid"])){
                            if(str_contains($organization["supannrefid"], 'CNRS')){
                                $dataProcess['labintel'] = $refId;
                            }

                            if(str_contains($organization["supannrefid"], 'RNSR')){
                                $dataProcess['rnsr'] = $refId;
                            }
                        }
                    }

                    $dataProcess['ldapsupanncodeentite'] = $organization["supanncodeentite"];

                    if(isset($organization["postaladdress"])) {
                        $address = explode("$",$organization["postaladdress"]);
                        $postalCodeCity = explode(" ", $address[2]);
                        $makeCity = "";

                        for($i=1;$i<count($postalCodeCity);$i++){
                            $makeCity .= $postalCodeCity[$i];

                            if($i<count($postalCodeCity)-1){
                                $makeCity .= " ";
                            }
                        }

                        $dataProcess['address'] = (object) array(
                            "address1" => $address[0],
                            "address2" => $address[1],
                            "zipcode" => isset($postalCodeCity[0]) ? $postalCodeCity[0] : "",
                            "country" => isset($address[3]) ? $address[3] : "",
                            "city" => $makeCity,
                            "address3" => ""
                        );
                    }

                    $data[] = (object) $dataProcess;
                }
            }

        } catch (\Exception $e) {
            throw new \Exception("Impossible de charger des données depuis  : " . $e->getMessage());
        }

        if( !is_array($data) ){
            throw new \Exception("LDAP n'a pas retourné un tableau de donnée");
        }

        // ...
        $this->syncAll($data, $this->getOrganizationRepository(), $report, $this->getOption('force', false));

        return $report;
    }

    function verifyTypes($supannType){

        foreach($this->arrayTypes as $typesCode){
            if($this->configFile[$typesCode."_array"] != ""){
                $explodeTypes = explode(",", $this->configFile[$typesCode."_array"]);

                foreach($explodeTypes as $codeSupann){
                    if($codeSupann == $supannType){
                        return $this->configFile[$typesCode."_id"];
                    }
                }
            }
        }

        return $this->configFile["inconnue_id"];
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
            $nbAjouts = 0;
            $nbMisaJour = 0;
            foreach( $organizationsData as $data ){
                try {
                    $iud = $data->code;
                    $organization = $repository->getObjectByConnectorID('ldap', $iud);
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
                        $nbAjouts++;
                        $report->addadded(sprintf("%s a été ajouté.", $organization->log()));
                    } else {
                        $nbMisaJour++;
                        $report->addupdated(sprintf("%s a été mis à jour.", $organization->log()));
                    }

                } else {
                    $report->addnotice(sprintf("%s est à jour.", $organization->log()));
                }
            }
        } catch (\Exception $e ){
            $report->adderror($e->getMessage());
        }


        $report->addnotice(sprintf("%s ajout(s) d'organisations.",$nbAjouts ));
        $report->addnotice(sprintf("%s mise(s) à jour d'organisations.",$nbMisaJour ));
        $report->addnotice("FIN du traitement...");
        return $report;
    }

    private function hydrateWithDatas( Organization $organization, $data ){
        return $this->factory()->hydrateWithDatas($organization, $data, "ldap");
    }

    function syncOrganization(Organization $organization)
    {
        if ($organization->getConnectorID('ldap')) {
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

    /**
     * Retourne le contenu depuis la source
     *
     * @return bool|string
     * @throws OscarException
     */
    public function getFileConfigContent()
    {
        $file = realpath(__DIR__.'/../../') . "/../../../config/connectors/organization_ldap.yml";
        if (!is_readable($file)) {
            throw new OscarException(sprintf("Impossible de lire le fichier '%s'.",
                $file));
        }

        return file_get_contents($file);
    }

    public function getFileConfig(){
        return $this->getFileConfigContent();
    }
}