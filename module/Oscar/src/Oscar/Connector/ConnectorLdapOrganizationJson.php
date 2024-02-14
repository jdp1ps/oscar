<?php
/**
 * Created by PhpStorm.
 * User: Sisomolida HING
 * Date: 14/02/24
 * Time: 10:52
 */

namespace Oscar\Connector;


use Doctrine\ORM\EntityManager;
use Oscar\Connector\DataAccessStrategy\HttpAuthBasicStrategy;
use Oscar\Connector\DataAccessStrategy\IDataAccessStrategy;
use Oscar\Connector\DataExtractionStrategy\LdapExtractionStrategy;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationLdap;
use Oscar\Entity\OrganizationRepository;
use Oscar\Exception\OscarException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Zend\Ldap\Exception\LdapException;
use Zend\Ldap\Ldap;

class ConnectorLdapOrganizationJson extends AbstractConnectorOscar
{
    private $editable;
    private $connectorID;
    private $configData = null;

    private $configPath = null;
    private $configFile;

    //Fonction obligatoire pour la configuration des connecteurs
    public function setConfigData($configData){
        $this->configData = $configData;
    }

    //Fonction obligatoire pour la configuration des connecteurs
    public function getConfigData(){
        if(is_null($this->configData)){
            $this->configPath = realpath(__DIR__.'/../../') . "/../../../config/connectors/organization_ldap.yml";
            $this->configData = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPath));
        }

        return $this->configData;
    }

    //Fonction obligatoire pour la configuration des connecteurs
    public function setEditable($editable){
        $this->editable = $editable;
    }

    //Fonction obligatoire pour la configuration des connecteurs
    public function getEditable(){
        return $this->editable;
    }

    public function setConnectorId($connectorId){
        $this->connectorID = $connectorId;
    }

    /**
     * @return OrganizationRepository
     */
    public function getRepository(): OrganizationRepository
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository(Organization::class);
    }

    protected function getServiceLocator(): \Zend\ServiceManager\ServiceManager
    {
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

    /**
     * @throws OscarException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws LdapException
     * @throws \Exception
     */
    function execute($force = true)
    {
        $this->setConnectorId('organization_ldap');
        $moduleOptions = $this->getServicemanager()->get('unicaen-app_module_options');

        $this->configPath = realpath(__DIR__.'/../../') . "/../../../config/connectors/organization_ldap.yml";
        $this->configFile = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPath));

        $configLdap = $moduleOptions->getLdap();
        $ldap = $configLdap['connection']['default']['params'];

        $dataStructureFromLdap = new OrganizationLdap();
        $dataStructureFromLdap->setConfig($configLdap);

        $dataStructureFromLdap->setLdap(new Ldap($ldap));

        $report = new ConnectorRepport();

        // Récupération des données
        try {
            $filtered = $this->configFile["ldap_filter"];
            $data = array();

            $extractorLdap = new LdapExtractionStrategy($this->getServicemanager());
            $connectorLdap = $extractorLdap->initiateLdapOrganization($configLdap, $ldap);

            foreach($filtered as $filter) {
                $dataOrg = $connectorLdap->findOneByFilter($filter);

                foreach($dataOrg as $organization){
                    $dataProcess = $extractorLdap->parseOrganizationLdap($organization);
                    $data[] = (object) $dataProcess;
                }
            }

            $extractorLdap->syncAllOrganizations($data, $this->getEntityManager()->getRepository(Organization::class), $report);
        } catch (\Exception $e) {
            throw new \Exception("Impossible de charger des données depuis  : " . $e->getMessage());
        }

        if( !is_array($data) ){
            throw new \Exception("LDAP n'a pas retourné un tableau de donnée");
        }

        return $report;
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
}