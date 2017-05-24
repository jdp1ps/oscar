<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-30 14:58
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use UnicaenApp\Mapper\Ldap\People;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceManager;

class ConnectorPersonREST implements IConnectorPerson, ServiceLocatorAwareInterface
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
        return "uid";
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

    /**
     * @param PersonRepository $personRepository
     * @param bool $force
     * @return ConnectorRepport
     * @throws \Oscar\Exception\OscarException
     */
    function syncPersons(PersonRepository $personRepository, $force)
    {
        $repport = new ConnectorRepport();

        $url = $this->getParameter('url_persons');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        $return = curl_exec($curl);
        curl_close($curl);

        foreach( json_decode($return) as $personData ){

            try {
                /** @var Person $personOscar */
                $personOscar = $personRepository->getPersonByConnectorID($this->getName(),
                    $personData->uid);
                $action = "update";
            } catch( NoResultException $e ){
                $personOscar = $personRepository->newPersistantPerson();
                $action = "add";
            }
            if($personOscar->getDateSyncLdap() < $personData->dateupdated || $force == true ){

                $personOscar = $this->hydratePersonWithDatas($personOscar, $personData);

                $personRepository->flush($personOscar);
                if( $action == 'add' ){
                    $repport->addadded(sprintf("%s a été ajouté.", $personOscar->log()));
                } else {
                    $repport->addupdated(sprintf("%s a été mis à jour.", $personOscar->log()));
                }

            } else {
                $repport->addnotice(sprintf("%s est à jour.", $personOscar->log()));
            }
        }
        return $repport;
    }

    private function hydratePersonWithDatas( Person $personOscar, $personData ){
        return $personOscar->setConnectorID($this->getName(), $personData->uid)
            ->setLadapLogin($personData->login)
            ->setFirstname($personData->firstname)
            ->setLastname($personData->lastname)
            ->setEmail($personData->mail)
            ->setHarpegeINM($personData->inm)
            ->setPhone($personData->phone)
            ->setDateSyncLdap(new \DateTime())
            ->setLdapStatus($personData->status)
            ->setLdapAffectation($personData->affectation)
            ->setLdapSiteLocation($personData->structure)
            ->setLdapMemberOf($personData->groups);
    }

    function syncPerson(Person $person)
    {
        if ($person->getConnectorID($this->getName())) {

            $url = sprintf($this->getParameter('url_person'), $person->getConnectorID($this->getName()));
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_COOKIESESSION, true);
            $return = curl_exec($curl);
            curl_close($curl);

            $personData = json_decode($return);
            return $this->hydratePersonWithDatas($person, $personData);

        } else {
            throw new \Exception('Impossible de synchroniser la personne ' . $person);
        }

    }

    /**
     * @return \UnicaenApp\Mapper\Ldap\People
     */
    protected function getServiceLdap()
    {
        return $this->getServiceLocator()->get('ldap_people_service')->getMapper();
    }
}