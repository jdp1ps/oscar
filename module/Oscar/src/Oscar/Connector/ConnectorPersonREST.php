<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-30 14:58
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Monolog\Logger;
use mysql_xdevapi\Exception;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Role;
use Oscar\Exception\ConnectorException;
use Oscar\Utils\PhpPolyfill;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceManager;

class ConnectorPersonREST implements IConnectorPerson, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait, ConnectorParametersTrait;

    private $editable = false;
    private $options;

    /** @var  ConnectorPersonHydrator */
    private $personHydrator = null;

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
        $this->options = [];
    }


    public function execute( $force = false)
    {
        $personRepository = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository(Person::class);

        return $this->syncPersons($personRepository, $force);
    }

    /**
     * @return ConnectorPersonHydrator
     */
    public function getPersonHydrator()
    {
        if( $this->personHydrator === null ){
            $this->personHydrator = new ConnectorPersonHydrator(
                $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')
            );
            $this->personHydrator->setPurge($this->getOptionPurge());
        }
        return $this->personHydrator;
    }

    /**
     * @param PersonRepository $personRepository
     * @param bool $force
     * @return ConnectorRepport
     * @throws \Oscar\Exception\OscarException
     */
    function syncPersons(PersonRepository $personRepository, $force)
    {
        if( $this->getOptionPurge() ){
            $exist = $personRepository->getUidsConnector($this->getName());
        }

        $repport = new ConnectorRepport();
        $this->getPersonHydrator()->setPurge($this->getOptionPurge());

        $url = $this->getParameter('url_persons');

        $repport->addnotice("REQUEST : " . $url);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        $return = curl_exec($curl);

        if( false === $return ){
            // @todo Trouver un moyen de faire remonter une erreur plus "causante"
            $this->getServiceLocator()
                ->get('Logger')
                ->error(sprintf(
                    "Accès connector '%s' impossible : %s",
                    $this->getName(),
                    curl_error($curl)
                ));
            throw new ConnectorException(sprintf("Le connecteur %s n'a pas fournis les données attendues", $this->getName()));
        }
        curl_close($curl);

        /////////////////////////////////////
        ////// Patch 2.7 "Lewis" GIT#286 ////
        try {
            $json = PhpPolyfill::jsonDecode($return);
            $personsDatas = null;

            if( is_object($json) && property_exists($json, 'persons') ){
                $personsDatas = $json->persons;
            } else {
                $personsDatas = $json;
            }

            if( !is_array($personsDatas) ){
                throw new \Exception("L'API n'a pas retourné un tableau de donnée");
            }
            $repport->addnotice(count($personsDatas). " résultat(s) a traiter.");
            ////////////////////////////////////

            foreach( $personsDatas as $personData ){

                if( ! property_exists($personData, 'uid') ){
                    $repport->addwarning(sprintf("Les donnèes %s n'ont pas d'UID.", print_r($personData, true)));
                    continue;
                }

                if( $this->getOptionPurge() ){
                    $uid = $personData->uid;
                    if( ($index = array_search($uid, $exist)) >= 0 ){
                        array_splice($exist, $index, 1);
                    }
                }

                try {
                    /** @var Person $personOscar */
                    $personOscar = $personRepository->getPersonByConnectorID($this->getName(),
                        $personData->uid);
                    $action = "update";
                } catch( NoResultException $e ){
                    $personOscar = $personRepository->newPersistantPerson();
                    $action = "add";

                } catch( NonUniqueResultException $e ){
                    $repport->adderror(sprintf("La personne avec l'ID %s est en double dans oscar.", $personData->uid));
                    continue;
                }

                if( $personData->dateupdated == null
                    || $personOscar->getDateSyncLdap() == null
                    || $personOscar->getDateSyncLdap() < $personData->dateupdated
                    || $force == true )
                {
                    $personOscar = $this->getPersonHydrator()->hydratePerson($personOscar, $personData, $this->getName());

                    $repport->addRepport($this->getPersonHydrator()->getRepport());

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

            if( $this->getOptionPurge() ){
                foreach ($exist as $uid){
                    $personOscarToDelete = $personRepository->getPersonByConnectorID($this->getName(), $uid);
                    try {
                        // todo Gérer les suppression
                        // $personRepository->removePerson($personOscarToDelete);
                        $repport->addremoved("Suppression (non implémentée) de $personOscarToDelete");
                    } catch (\Exception $e){
                        $repport->addwarning("$personOscarToDelete n'a pas été supprimé car il est actif dans les activités : " . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e ){
            throw new \Exception("Impossible de synchroniser les personnes : " . $e->getMessage());
        }

        $personRepository->flush(null);

        return $repport;
    }

    function syncPerson(Person $person)
    {
        if ($person->getConnectorID($this->getName())) {

            $url = sprintf($this->getParameter('url_person'), $person->getConnectorID($this->getName()));
            $this->getLogger()->info("connector request : " . $url);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_COOKIESESSION, true);
            $return = curl_exec($curl);
            curl_close($curl);
            if( false === $return ){
                $message = sprintf("Le connecteur %s n'a pas fournis les données attendues", $this->getName());
                $this->getLogger()->error($message . " - " . cubrid_error_msg());
                throw new ConnectorException($message);
            }

            $personData = json_decode($return);
            if( $personData === null ){
                // @todo Trouver un moyen de faire remonter une erreur plus "causante"
                $message = sprintf("Aucune données retournée par le connecteur%s.", $this->getName());
                $this->getLogger()->error($message . " - " . print_r($return, true));
                throw new ConnectorException($message);
            }

            return $this->getPersonHydrator()->hydratePerson($person, $personData, $this->getName());

        } else {
            throw new \Exception('Impossible de synchroniser la personne ' . $person);
        }

    }

    public function setOption($optionName, $optionValue){
        $this->options[$optionName] = $optionValue;
    }

    public function getOption($optionName, $defaultValue=null){
        if( array_key_exists($optionName, $this->options) ){
            return $this->options[$optionName];
        }
        return $defaultValue;
    }

    public function getOptionPurge(){
        return $this->getOption('purge', false);
    }

    public function setOptionPurge( $boolean ){
        return $this->setOption('purge', $boolean);
    }


    /**
     * @return Logger
     */
    protected function getLogger(){
        return $this->getServiceLocator()->get('Logger');
    }

    /**
     * @return \UnicaenApp\Mapper\Ldap\People
     */
    protected function getServiceLdap()
    {
        return $this->getServiceLocator()->get('ldap_people_service')->getMapper();
    }
}