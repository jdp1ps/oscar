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
use Oscar\Entity\OrganizationPerson;
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
        "filtrage" => "&(objectClass=inetOrgPerson)(eduPersonAffiliation=member)(eduPersonAffiliation=researcher),&(objectClass=inetOrgPerson)(eduPersonAffiliation=member)(eduPersonAffiliation=emeritus),&(objectClass=inetOrgPerson)(eduPersonAffiliation=member)(supannCodePopulation={SUPANN}AGA*),&(objectClass=inetOrgPerson)(eduPersonAffiliation=member)(eduPersonAffiliation=staff)"
    );
    private $configPath = null;
    private $configFile;
    private $mappingRolePerson = array(
        //ID 21 correspond au role "Directeur de laboratoire" en base de donnée
        "{UAI:0751717J:HARPEGE.FCSTR}530" => 21
    );

    const LDAP_FILTER_ALL = '*';

    public function getDataAccess(): IDataAccessStrategy
    {
        return new HttpAuthBasicStrategy($this);
    }

    public function getConfigData()
    {
        $this->configFile = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPath));

        if($this->configLdap["filtrage"] == null){
            $configFiltre["filtrage"] = $this->configFile['filtre_ldap'];
            $this->updateParameters($configFiltre);
        }

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
        $this->configPath = realpath(__DIR__.'/../../') . "/../../../config/connectors/person_ldap.yml";
        $this->configFile = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPath));
        $this->shortName = $this->configLdap["type"];

        if($this->configLdap["filtrage"] == null){
            $configFiltre["filtrage"] = $this->configFile['filtre_ldap'];
            $this->updateParameters($configFiltre);
        }

        $configLdap = $moduleOptions->getLdap();
        $ldap = $configLdap['connection']['default']['params'];
        $report = new ConnectorRepport();

        // Récupération des données
        try {
            set_time_limit(600);

            $filtrage = $this->configLdap["filtrage"];
            $dataFiltrage = explode(",", $filtrage);

            foreach($dataFiltrage as $filtre){
                $dataPeopleFromLdap = new PersonLdap();
                $dataPeopleFromLdap->setConfig($configLdap);
                $dataPeopleFromLdap->setLdap(new Ldap($ldap));
                $data = $dataPeopleFromLdap->findAll($filtre);
                $personsData = array();

                foreach($data as $person){
                    $person['firstname'] = $person['givenname'];
                    $person['lastname'] = $person['sn'];
                    $person['login'] = $person['uid'];
                    $person['codeHarpege'] = isset($person['supannentiteaffectationprincipale'])? $person['supannentiteaffectationprincipale'] : "" ;

                    if(isset($person['mail'])){
                        $person['email'] = $person['mail'];
                        $person['emailPrive'] = $person['mail'];
                    } else {
                        if(isset($person['edupersonprincipalname'])) {
                            $person['email'] = $person['edupersonprincipalname'];
                            $person['emailPrive'] = $person['edupersonprincipalname'];
                        }
                    }

                    $person['phone'] = isset($person['telephonenumber']) ? $person['telephonenumber'] : "" ;
                    $person['projectAffectations'] = $person['edupersonaffiliation'];
                    $person['ldapsitelocation'] = isset($person['buildingName']) ? $person['buildingName']: null;

                    if(isset($person["supannroleentite"])){
                        $person['supannroleentite'] = $person["supannroleentite"];
                    }

                    if(isset($person['supannentiteaffectation']) && is_array($person['supannentiteaffectation'])){


                        $nbAffectation = count($person['supannentiteaffectation']);
                        $nbTmp = 0;
                        $person['affectation'] = "";
                        $person['organizations'] = array();

                        foreach($person['supannentiteaffectation'] as $affectation){
                            $person['affectation'] .= $affectation;
                            $nbTmp++;

                            if($nbTmp < $nbAffectation){
                                $person['affectation'] .= ',';
                            }
                        }
                    } else {
                        //OrganizationPerson
                        if(isset($person['supannentiteaffectation'])) {
                            $person['affectation'] = $person['supannentiteaffectation'];
                        }
                    }

                    $person['activities'] = null;
                    $person['ladapLogin'] = $person['supannaliaslogin'];
                    $person['dateupdated'] = null;
                    $personsData[] = (object) $person;
                }

                $this->syncPersons($personsData, $this->getPersonRepository(), $report, $this->getOption('force', false));
            }

        } catch (\Exception $e) {
            throw new \Exception("Impossible de charger des données depuis : " . $e->getMessage());
        }

        if( !is_array($data) ){
            throw new \Exception("LDAP n'a pas retourné un tableau de donnée");
        }

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

                    if(isset($personData->supannroleentite)){
                        $rolesPerson = $personData->supannroleentite;
                        $organizationRepository = $this->getOrganizationRepository();

                        if(is_array($rolesPerson)){
                            foreach($rolesPerson as $role){
                                $substringRole = substr($role, 1, strlen($role)-2);
                                $explodeRole = explode("][",$substringRole);
                                $exactRole = substr($explodeRole[0],5,strlen($explodeRole[0]));
                                //$exactType = substr($explodeRole[1],5,strlen($explodeRole[1]));
                                $exactCode = substr($explodeRole[2],5,strlen($explodeRole[2]));

                                if(array_key_exists($exactRole, $this->mappingRolePerson)){
                                    $dataOrg = $organizationRepository->getOrganisationByCodeNullResult($exactCode);
                                    $dataOrgPer = $organizationRepository->getOrganisationPersonByPersonNullResult($personOscar);

                                    if($dataOrg != null){
                                        if($dataOrgPer == null){
                                            $dataOrgPer = new OrganizationPerson();
                                        }

                                        $organizationRepository->saveOrganizationPerson(
                                            $dataOrgPer,
                                            $personOscar,
                                            $dataOrg,
                                            $this->mappingRolePerson[$exactRole]
                                        );
                                    }
                                }
                            }
                        } else {
                            $substringRole = substr($rolesPerson, 1, strlen($rolesPerson)-2);
                            $explodeRole = explode("][",$substringRole);
                            $exactRole = substr($explodeRole[0],5,strlen($explodeRole[0]));
                            //$exactType = substr($explodeRole[1],5,strlen($explodeRole[1]));
                            $exactCode = substr($explodeRole[2],5,strlen($explodeRole[2]));

                            if(array_key_exists($exactRole, $this->mappingRolePerson)){
                                $dataOrg = $organizationRepository->getOrganisationByCodeNullResult($exactCode);
                                $dataOrgPer = $organizationRepository->getOrganisationPersonByPersonNullResult($personOscar);

                                if($dataOrg != null){
                                    if($dataOrgPer == null){
                                        $dataOrgPer = new OrganizationPerson();
                                    }

                                    $organizationRepository->saveOrganizationPerson(
                                        $dataOrgPer,
                                        $personOscar,
                                        $dataOrg,
                                        $this->mappingRolePerson[$exactRole]
                                    );
                                }
                            }
                        }


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

                $nbPersons = count($personsDatas);
                $repport->addnotice("$nbPersons personnes ont été ajouté(s) ou mise(s) à jour");

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

    public function getOrganizationRepository(){
        return $this->getEntityManager()->getRepository(Organization::class);
    }

    /**
     * Retourne le contenu depuis la source
     *
     * @return bool|string
     * @throws OscarException
     */
    public function getFileConfigContent()
    {
        $file = realpath(__DIR__.'/../../') . "/../../../config/connectors/person_ldap.yml";
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