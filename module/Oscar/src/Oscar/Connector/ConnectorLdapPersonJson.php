<?php
/**
 * Created by PhpStorm.
 * User: Sisomolida HING
 * Date: 14/02/24
 * Time: 10:52
 */

namespace Oscar\Connector;


use Doctrine\ORM\EntityManager;
use Exception;
use Oscar\Connector\DataAccessStrategy\HttpAuthBasicStrategy;
use Oscar\Connector\DataAccessStrategy\IDataAccessStrategy;
use Oscar\Connector\DataExtractionStrategy\LdapExtractionStrategy;
use Oscar\Entity\Organization;
use Oscar\Entity\Person;
use Oscar\Exception\OscarException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Zend\Ldap\Exception\LdapException;

class ConnectorLdapPersonJson extends AbstractConnectorOscar
{
    private $configData = null;
    private $configPath = null;

    public function getDataAccess(): IDataAccessStrategy
    {
        return new HttpAuthBasicStrategy($this);
    }

    //Fonction obligatoire pour la configuration des connecteurs
    public function setConfigData($configData): void
    {
        $this->configData = $configData;
    }

    //Fonction obligatoire pour la configuration des connecteurs
    public function getConfigData(){
        if(is_null($this->configData)){
            $this->configPath = realpath(__DIR__) . "/../../../../../config/connectors/person_ldap.yml";
            $this->configData = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPath));
        }

        return $this->configData;
    }

    //Fonction obligatoire pour la configuration des connecteurs
    public function setEditable($editable): void
    {
        $this->editable = $editable;
    }

    //Fonction obligatoire pour la configuration des connecteurs
    public function getEditable(){
        return $this->editable;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws LdapException
     */
    public function execute($force = true): ConnectorRepport
    {
        $moduleOptions = $this->getServiceManager()->get('unicaen-app_module_options');
        $configPath = realpath(__DIR__.'/../../') . "/../../../config/connectors/person_ldap.yml";
        $configFile = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($configPath));

        $configLdap = $moduleOptions->getLdap();
        $ldap = $configLdap['connection']['default']['params'];
        $report = new ConnectorRepport();
        $data = null;

        // Récupération des données
        try {
            $dataFiltered = $configFile["ldap_filter"];
            $extractorLdap = new LdapExtractionStrategy($this->getServicemanager());

            if(!is_array($dataFiltered)){
                $dataFiltered = array($dataFiltered);
            }

            foreach($dataFiltered as $filter){
                $connectorLdap = $extractorLdap->initiateLdapPerson($configLdap, $ldap);
                $data = $connectorLdap->findAll($filter);
                $personsData = array();

                foreach($data as $person){
                    $personObj = $extractorLdap->parseLdapPerson($person);
                    $personsData[] = (object) $personObj;
                }

                $extractorLdap->syncPersons($personsData, $this->getPersonRepository(), $report);
            }

        } catch (LdapException $e) {
            $report->addwarning("Impossible de charger des données depuis Ldap");
        }

        return $report;
    }

    public function getPersonRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->getEntityManager()->getRepository(Person::class);
    }

    public function getEntityManager(): EntityManager
    {
        return $this->getServiceManager()->get('Doctrine\ORM\EntityManager');
    }

    public function getOrganizationRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->getEntityManager()->getRepository(Organization::class);
    }

    /**
     * Retourne le contenu depuis la source
     *
     * @return bool|string
     * @throws OscarException
     */
    public function getFileConfigContent(): string
    {
        $file = realpath(__DIR__.'/../../') . "/../../../config/connectors/person_ldap.yml";
        if (!is_readable($file)) {
            throw new OscarException(sprintf("Impossible de lire le fichier '%s'.",
                $file));
        }

        return file_get_contents($file);
    }

    /**
     * @throws OscarException
     */
    public function getPathAll(): string
    {
        return $this->getParameter('filter');
    }

    /**
     * @throws OscarException
     */
    public function getPathSingle($remoteId): string
    {
        return sprintf($this->getParameter('filter'), $remoteId);
    }
}
