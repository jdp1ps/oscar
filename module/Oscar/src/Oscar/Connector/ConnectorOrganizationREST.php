<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-30 14:58
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Exception\ConnectorException;
use Oscar\Factory\JsonToOrganization;
use UnicaenApp\Mapper\Ldap\People;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceManager;

class ConnectorOrganizationREST implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait, ConnectorParametersTrait;

    private $editable = false;

    public function setEditable($editable){
        $this->editable = $editable;
    }

    public function isEditable(){
        return $this->editable;
    }

    function getName()
    {
        return "rest";
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

    public function init(ServiceManager $sm, $configFilePath)
    {
        $this->setServiceLocator($sm);
        $this->loadParameters($configFilePath);
    }

    /**
     * @return OrganizationRepository
     */
    public function getRepository()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository(Organization::class);
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

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        $return = curl_exec($curl);
        curl_close($curl);

        if( false === $return ){
            throw new ConnectorException(sprintf("Le connecteur %s n'a pas fournis les données attendues", $this->getName()));
        }

        if( count($return) > 0 ){
            foreach( json_decode($return) as $data ){

                try {
                    /** @var Person $personOscar */
                    $organization = $repository->getObjectByConnectorID($this->getName(), $data->code);
                    $action = "update";
                } catch( NoResultException $e ){
                    $organization = $repository->newPersistantObject();
                    $action = "add";
                }

                if($organization->getDateUpdated() < new \DateTime($data->dateupdated) || $force == true ){

                    $organization = $this->hydrateWithDatas($organization, $data);

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
        } else {
            $repport->adderror("Le service REST n'a retourné aucun résultat.");
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

            $url = sprintf($this->getParameter('url_organization'), $organization->getConnectorID($this->getName()));
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_COOKIESESSION, true);
            $return = curl_exec($curl);
            curl_close($curl);

            $organizationData = json_decode($return);
            return $this->hydrateWithDatas($organization, $organizationData);

        } else {
            throw new \Exception('Impossible de synchroniser la structure ' . $organization);
        }

    }
}