<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-15 08:46
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;

use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceManager;

class ConnectorPersonOracle extends AbstractConnectorOracle implements IConnectorPerson
{
    use ServiceLocatorAwareTrait;

    /**
     * ConnectorPersonHarpege constructor.
     */
    // array $params, array $fieldsConfiguration
    public function __construct()
    {
        parent::__construct();
    }

    public function init(ServiceManager $sm, $configFilePath)
    {
        parent::init($sm, $configFilePath);
        $this->setQueryOne($this->getParam('queryPerson'));
        $this->setQueryAll($this->getParam('queryPersons'));
        $this->configureFieldUpdate($this->getParam('dateUpdatedField'));

        $this->buildFieldConfiguration($this->getParam('relations')['value']);
        //$this->setHydratationPostProcess($this->getParam('hydratationPostProcess'));
    }

    /**
     * @return PersonRepository
     */
    public function getPersonRepository()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository(Person::class);
    }


    public function execute()
    {
        $personRepository = $this->getPersonRepository();

        return $this->syncPersons($personRepository, true);
    }



    public function getPersonData( $idConnector ){

        $stid = $this->query(sprintf($this->getParam('queryPerson'), $idConnector));
        if( !$stid ){
            throw new Exception('Erreur de requète');
        }
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            return $row;
        }
        return null;
    }

    public function syncPersons( PersonRepository $repository, $force){
        return $this->syncAll($repository, $force);
    }

    protected function getDateTimeFrom( $format, $value ){
        return \DateTime::createFromFormat($format, $value);
    }

    public function syncPerson( Person $person ){
        $datas = $this->getPersonData($person->getConnectorID($this->getName()));
        $this->hydrateObjectWithRemote($person, $datas);
        $person->setDateUpdated(new \DateTime());
        return $person;
    }

    function getRemoteID()
    {
        return 'HARPEGEID';
    }

    public function getName()
    {
        return "harpege";
    }
}