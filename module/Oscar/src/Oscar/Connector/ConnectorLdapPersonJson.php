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
use Oscar\Entity\Person;
use Oscar\Entity\PersonLdap;
use Oscar\Entity\PersonRepository;
use Oscar\Exception\OscarException;
use UnicaenApp\Mapper\Ldap\People;
use Zend\Ldap\Ldap;

class ConnectorLdapPersonJson extends AbstractConnectorOscar
{
    private $personHydrator;
    private $editable;
    private $configLdap = array(
        "type" => "person_ldap",
        "label" => "Person Ldap",
        "filtrage" => null
    );
    private $configPath = "/var/www/html/oscar/config/autoload/../connectors/person_ldap.yml";
    private $configFile;

    const LDAP_FILTER_ALL = '*';

    public function getDataAccess(): IDataAccessStrategy
    {
        return new HttpAuthBasicStrategy($this);
    }

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

    public function updateParameters($config)
    {
        $dataConfig = $config;
        if(!is_array($config))
            $dataConfig = $config->toArray();

        $this->configLdap["filtrage"] = $dataConfig["filtrage"];
    }

    function execute($force = true)
    {
        $moduleOptions = $this->getServicemanager()->get('unicaen-app_module_options');
        $this->configFile = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPath));

        if($this->configLdap["filtrage"] == null){
            $configFiltre["filtrage"] = $this->configFile['filtre_ldap'];
            $this->updateParameters($configFiltre);
        }

        $configLdap = $moduleOptions->getLdap();
        $ldap = $configLdap['connection']['default']['params'];

        $dataPeopleFromLdap = new PersonLdap();
        $dataPeopleFromLdap->setConfig($configLdap);
        $dataPeopleFromLdap->setLdap(new Ldap($ldap));

        $report = new ConnectorRepport();

        // Récupération des données
        try {

            $personsData = array();
            $filtrage = $this->configLdap["filtrage"];
            $dataFiltrage = explode(",", $filtrage);

            $data = $dataPeopleFromLdap->findAll($this->configLdap["filtrage"]);
            //$data = $dataPeopleFromLdap->findAllByAffectation($dataFiltrage);

            foreach($data as $person){
                $person['firstname'] = $person['sn'];
                $person['lastname'] = $person['givenname'];
                $person['codeHarpege'] = $person['supannentiteaffectationprincipale'];
                $person['email'] = $person['edupersonprincipalname'];
                $person['emailPrive'] = $person['edupersonprincipalname'];
                $person['phone'] = $person['telephonenumber'];
                $person['projectAffectations'] = $person['edupersonaffiliation'];
                $person['activities'] = null;
                //$person['ldapStatus'] = $person['sn'];
                //$person['ldapSiteLocation'] = $person['sn'];
                //$person['ldapAffectation'] = $person['sn'];
                $person['ladapLogin'] = $person['supannaliaslogin'];
                $personsData[] = (object) $person;
            }



        } catch (\Exception $e) {
            throw new \Exception("Impossible de charger des données depuis : " . $e->getMessage());
        }

        if( !is_array($data) ){
            throw new \Exception("LDAP n'a pas retourné un tableau de donnée");
        }

        $this->syncPersons($personsData, $this->getPersonRepository(), $report, $this->getOption('force', false));

        return $report;
    }

    /**
     * @return ConnectorPersonHydrator
     */
    public function getPersonHydrator()
    {
        if( $this->personHydrator === null ){
            $this->personHydrator = new ConnectorPersonHydrator(
                $this->getEntityManager()
            );
            $this->personHydrator->setPurge($this->getOptionPurge());
        }
        return $this->personHydrator;
    }

    /**
     * @return PersonRepository
     */
    public function getPersonRepository(){
        return $this->getEntityManager()->getRepository(Person::class);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(){
        return $this->getServiceManager()->get('Doctrine\ORM\EntityManager');
    }


    public function syncPersons($personsDatas, PersonRepository $personRepository, ConnectorRepport &$repport, $force)
    {
        if( $this->getOptionPurge() ){
            $exist = $personRepository->getUidsConnector($this->getName());
            $repport->addnotice(sprintf(_("Il y'a %s personne(s) référencées dans Oscar pour le connecteur '%s'."), count($exist), $this->getName()));
        }

        $repport->addnotice(count($personsDatas). " résultat(s) reçus vont être traité.");

        $this->getPersonHydrator()->setPurge($this->getOptionPurge());

        try {

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
                    $personOscar = $personRepository->getPersonByConnectorID($this->getName(), $personData->uid);
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
                    || $personOscar->getDateSyncLdap()->format('Y-m-d') < $personData->dateupdated
                    || $force == true )
                {
                    $personOscar = $this->getPersonHydrator()->hydratePerson($personOscar, $personData, $this->getName());
                    if( $personOscar == null ){
                        throw new \Exception("WTF $action");
                    }

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

                $idsToDelete = [];

                foreach ($exist as $uid){
                    try {
                        /** @var Person $personOscarToDelete */
                        $personOscarToDelete = $personRepository->getPersonByConnectorID($this->getName(), $uid);

                        $activeIn = [];

                        if( count($personOscarToDelete->getActivities()) > 0 ){
                            $activeIn[] = "activité";
                        }
                        if( count($personOscarToDelete->getProjectAffectations()) > 0 ){
                            $activeIn[] = "projet";
                        }
                        if( count($personOscarToDelete->getOrganizations()) > 0 ){
                            $activeIn[] = "organisation";
                        }

                        if( count($activeIn) == 0 ){
                            $idsToDelete[] = $personOscarToDelete->getId();
                        } else {
                            $repport->addwarning("$personOscarToDelete n'a pas été supprimé car il est actif dans : " . implode(', ', $activeIn));
                        }

                    } catch (\Exception $e){
                        $repport->adderror("$personOscarToDelete n'a pas été supprimé car il est actif dans les activités : " . $e->getMessage());
                    }
                }

                foreach ($idsToDelete as $idPerson) {
                    try {
                        $personRepository->removePersonById($idPerson);
                        $repport->addremoved("Suppression de person $idPerson : ");
                    } catch (\Exception $e) {
                        $repport->adderror("Impossible de supprimer la person $idPerson : " . $e->getMessage());
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
        $moduleOptions = $this->getServicemanager()->get('unicaen-app_module_options');

        $configLdap = $moduleOptions->getLdap();
        $ldap = $configLdap['connection']['default']['params'];

        $dataPeopleFromLdap = new People();
        $dataPeopleFromLdap->setConfig($configLdap);
        $dataPeopleFromLdap->setLdap(new Ldap($ldap));

        if ( ($remoteId = $person->getConnectorID($this->getName())) ) {
            //$personData = $this->getDataAccess()->getDataSingle($remoteId);
            $personData = $dataPeopleFromLdap->findOneByUid($remoteId);
            if( property_exists($personData, 'person') ){
                $personData = $personData->person;
            }
            return $this->getPersonHydrator()->hydratePerson($person, $personData, $this->getName());
        } else {
            throw new \Exception('Impossible de synchroniser la personne ' . $person);
        }
    }


    public function getPathAll(): string
    {
        return $this->getParameter('url_persons');
    }

    public function getPathSingle($remoteId): string
    {
        return sprintf($this->getParameter('url_person'), $remoteId);
    }
}