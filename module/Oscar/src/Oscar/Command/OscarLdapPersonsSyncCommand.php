<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Moment\Moment;
use Oscar\Connector\ConnectorPersonHydrator;
use Oscar\Connector\ConnectorRepport;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationLdap;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationType;
use Oscar\Entity\Person;
use Oscar\Entity\PersonLdap;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Role;
use Oscar\Exception\OscarException;
use Oscar\Service\ConnectorService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use UnicaenApp\Mapper\Ldap\People;
use UnicaenApp\Mapper\Ldap\Structure;
use UnicaenApp\Entity\Ldap\Structure as LdapStructureModel;
use Zend\Ldap\Ldap;

class OscarLdapPersonsSyncCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'ldap:persons:sync';
    private $personHydrator;
    private $configPath = null;
    private $configFile;
    private $purge = false;
    private $configLdap = array(
        "type" => "person_ldap",
        "label" => "Person Ldap",
        "filtrage" => "&(objectClass=inetOrgPerson)(eduPersonAffiliation=member)(eduPersonAffiliation=researcher),&(objectClass=inetOrgPerson)(eduPersonAffiliation=member)(eduPersonAffiliation=emeritus),&(objectClass=inetOrgPerson)(eduPersonAffiliation=member)(supannCodePopulation={SUPANN}AGA*),&(objectClass=inetOrgPerson)(eduPersonAffiliation=member)(eduPersonAffiliation=staff)"
        //"filtrage" => "&(objectClass=inetOrgPerson)(eduPersonAffiliation=member)(eduPersonAffiliation=researcher)"
    );

    private $mappingRolePerson = array(
        //ID 21 correspond au role "Directeur de laboratoire" en base de donnée
        "{UAI:0751717J:HARPEGE.FCSTR}530" => 21
    );

    protected function configure()
    {
        $this
            ->setDescription("Synchronisation des personnes depuis LDAP")
        ;
        $this->configPath = realpath(__DIR__.'/../../') . "/../../../config/connectors/person_ldap.yml";
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Synchronisation LDAP des personnes");

        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        /** @var OrganizationService $organisationService */
        $organisationService = $this->getServicemanager()->get(OrganizationService::class);

        try {
            $moduleOptions = $this->getServicemanager()->get('unicaen-app_module_options');
            $this->configFile = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPath));

            $configLdap = $moduleOptions->getLdap();
            $ldap = $configLdap['connection']['default']['params'];

            $filtrage = $this->configFile['filtre_ldap'];
            $data = array();

            try {
                set_time_limit(600);

                $filtrage = $this->configLdap["filtrage"];
                $dataFiltrage = explode(",", $filtrage);

                foreach($dataFiltrage as $filtre){
                    $io->writeln("Exécution d'un filtre : veuillez patienter (ce script peut prendre une dizaine de minutes ... )");
                    $dataPeopleFromLdap = new PersonLdap();
                    $dataPeopleFromLdap->setConfig($configLdap);
                    $dataPeopleFromLdap->setLdap(new Ldap($ldap));
                    $data = $dataPeopleFromLdap->findAll($filtre);
                    $personsData = array();
                    $nbModif = 0;

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
                    $nbModif += count($personsData);
                    $this->syncPersons($personsData, $this->getEntityManager()->getRepository(Person::class), $io, false);
                }

                $io->writeln("Ajout(s) ou mise(s) à jour : $nbModif personnes");

            } catch (\Exception $e) {
                $io->error("Impossible de charger des données depuis : " . $e->getMessage());
            }

            if( !is_array($data) ){
                $io->error("LDAP n'a pas retourné un tableau de donnée");
            }

        } catch (\Exception $e ){
            $io->error($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function syncPersons($personsDatas, PersonRepository $personRepository, SymfonyStyle &$io, $force)
    {
        if( $this->purge ){
            $exist = $personRepository->getUidsConnector('ldap');
            $io->writeln(sprintf(_("Il y'a %s personne(s) référencées dans Oscar pour le connecteur '%s'."), count($exist), 'ldap'));
        }

        $io->writeln(count($personsDatas). " résultat(s) reçus vont être traité.");

        $this->getPersonHydrator()->setPurge($this->purge);

        try {

            foreach( $personsDatas as $personData ){

                if( ! property_exists($personData, 'uid') ){
                    $io->error(sprintf("Les donnèes %s n'ont pas d'UID.", print_r($personData, true)));
                    continue;
                }

                if( $this->purge ){
                    $uid = $personData->uid;
                    if( ($index = array_search($uid, $exist)) >= 0 ){
                        array_splice($exist, $index, 1);
                    }
                }

                try {
                    /** @var Person $personOscar */
                    $personOscar = $personRepository->getPersonByConnectorID('ldap', $personData->uid);
                    $action = "update";

                } catch( NoResultException $e ){
                    $personOscar = $personRepository->newPersistantPerson();
                    $action = "add";

                } catch( NonUniqueResultException $e ){
                    $io->error(sprintf("La personne avec l'ID %s est en double dans oscar.", $personData->uid));
                    continue;
                }

                if( $personData->dateupdated == null
                    || $personOscar->getDateSyncLdap() == null
                    || $personOscar->getDateSyncLdap()->format('Y-m-d') < $personData->dateupdated
                    || $force == true )
                {
                    $personOscar = $this->getPersonHydrator()->hydratePerson($personOscar, $personData, 'ldap');

                    if( $personOscar == null ){
                        $io->error("WTF $action");
                    }

                    if(isset($personData->supannroleentite)){
                        $rolesPerson = $personData->supannroleentite;
                        $organizationRepository = $this->getOrganizationRepository();

                        if(is_array($rolesPerson)){
                            foreach($rolesPerson as $role){
                                $substringRole = substr($role, 1, strlen($role)-2);
                                $explodeRole = explode("][",$substringRole);
                                $exactRole = substr($explodeRole[0],5,strlen($explodeRole[0]));
                                $exactType = substr($explodeRole[1],5,strlen($explodeRole[1]));
                                $exactCode = substr($explodeRole[2],5,strlen($explodeRole[2]));

                                if(array_key_exists($exactRole, $this->mappingRolePerson)){
                                    $dataOrg = $organizationRepository->getOrganisationByCodeNullResult($exactCode);

                                    if($dataOrg != null){
                                        //$organization = $organizationRepository->getObjectByConnectorID('ldap', $exactCode);
                                        //$objOrganization =new Organization();
                                        //$objOrganization->setConnectorID('ldap', $exactCode);
                                        //$organizationRepository->saveOrganizationPerson($personOscar,$dataOrg, $this->mappingRolePerson[$exactRole]);
                                    }
                                }
                            }
                        } else {
                            $substringRole = substr($rolesPerson, 1, strlen($rolesPerson)-2);
                            $explodeRole = explode("][",$substringRole);
                            $exactRole = substr($explodeRole[0],5,strlen($explodeRole[0]));
                            $exactType = substr($explodeRole[1],5,strlen($explodeRole[1]));
                            $exactCode = substr($explodeRole[2],5,strlen($explodeRole[2]));

                            if(array_key_exists($exactRole, $this->mappingRolePerson)){
                                $dataOrg = $organizationRepository->getOrganisationByCodeNullResult($exactCode);

                                if($dataOrg != null){
                                    //$organization = $organizationRepository->getObjectByConnectorID('ldap', $exactCode);
                                    //$objOrganization =new Organization();
                                    //$objOrganization->setConnectorID('ldap', $exactCode);
                                    //$organizationRepository->saveOrganizationPerson($personOscar,$dataOrg, $this->mappingRolePerson[$exactRole]);
                                }
                            }
                        }


                    }
                    $personRepository->flush($personOscar);

                    if( $action == 'add' ){
                        $io->writeln(sprintf("%s a été ajouté.", $personOscar->log()));
                    } else {
                        $io->writeln(sprintf("%s a été mis à jour.", $personOscar->log()));
                    }
                } else {
                    $io->writeln(sprintf("%s est à jour.", $personOscar->log()));
                }
            }

            if( $this->purge ){

                $idsToDelete = [];

                foreach ($exist as $uid){
                    try {
                        /** @var Person $personOscarToDelete */
                        $personOscarToDelete = $personRepository->getPersonByConnectorID('ldap', $uid);

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
                            $io->error("$personOscarToDelete n'a pas été supprimé car il est actif dans : " . implode(', ', $activeIn));
                        }

                    } catch (\Exception $e){
                        $io->error("$personOscarToDelete n'a pas été supprimé car il est actif dans les activités : " . $e->getMessage());
                    }
                }

                foreach ($idsToDelete as $idPerson) {
                    try {
                        $personRepository->removePersonById($idPerson);
                        $io->writeln("Suppression de person $idPerson : ");
                    } catch (\Exception $e) {
                        $io->error("Impossible de supprimer la person $idPerson : " . $e->getMessage());
                    }
                }

            }

            $nbPersons = count($personsDatas);
            $io->writeln("$nbPersons personnes ont été ajouté(s) ou mise(s) à jour");

        } catch (\Exception $e ){
            $io->error("Impossible de synchroniser les personnes : " . $e->getMessage());
        }

        $personRepository->flush(null);
    }

    public function getPersonHydrator()
    {
        if( $this->personHydrator === null ){
            $this->personHydrator = new ConnectorPersonHydrator(
                $this->getEntityManager()
            );
            $this->personHydrator->setPurge($this->purge);
        }
        return $this->personHydrator;
    }

    function hydrateWithDatas($object, $jsonData, $connectorName = null, SymfonyStyle $io)
    {
        if ($connectorName !== null) {
            $object->setConnectorID(
                $connectorName,
                $this->getFieldValue($jsonData, 'uid', null, $io)
            );
        }

        $object
            ->setDateUpdated(new \DateTime($this->getFieldValue($jsonData, 'dateupdate', null, $io)))
            ->setLabintel($this->getFieldValue($jsonData, 'labintel', null, $io))
            ->setShortName($this->getFieldValue($jsonData, 'shortname', null, $io))
            ->setCode($this->getFieldValue($jsonData, 'code', null, $io))
            ->setFullName($this->getFieldValue($jsonData, 'longname', null, $io))
            ->setPhone($this->getFieldValue($jsonData, 'phone', null, $io))
            ->setDescription($this->getFieldValue($jsonData, 'description', null, $io))
            ->setEmail($this->getFieldValue($jsonData, 'email', null, $io))
            ->setUrl($this->getFieldValue($jsonData, 'url', null, $io))
            ->setSiret($this->getFieldValue($jsonData, 'siret', null, $io))
            ->setType($this->getFieldValue($jsonData, 'type', null, $io))
            ->setTypeObj($this->getTypeObj($this->getFieldValue($jsonData, 'type', null, $io)))

            // Ajout de champs
            ->setDuns($this->getFieldValue($jsonData, 'duns', null, $io))
            ->setTvaintra($this->getFieldValue($jsonData, 'tvaintra', null, $io))
            ->setRnsr($this->getFieldValue($jsonData, 'rnsr', null, $io));

        if (property_exists($jsonData, 'address')) {
            $address = $jsonData->address;
            if (is_object($address)) {
                $object
                    ->setStreet1(property_exists($address, 'address1') ? $address->address1 : null)
                    ->setStreet2(property_exists($address, 'address2') ? $address->address2 : null)
                    ->setZipCode(property_exists($address, 'zipcode') ? $address->zipcode : null)
                    ->setCity(property_exists($address, 'city') ? $address->city : null)
                    ->setCountry(property_exists($address, 'country') ? $address->country : null)
                    ->setBp(property_exists($address, 'address3') ? $address->address3 : null);
            }
        }

        return $object;
    }

    protected function getTypeObj( string $typeLabel ) :?OrganizationType
    {
        $types = $this->getEntityManager()->getRepository(OrganizationType::class)->findAll();
        $allTypes = [];
        /** @var OrganizationType $organizationType */
        foreach ($types as $organizationType){
            $allTypes[$organizationType->getLabel()] = $organizationType;
        }

        if( is_array($allTypes) && array_key_exists($typeLabel, $allTypes) ){
            return $allTypes[$typeLabel];
        }
        return null;
    }

    function hydrateWithDatasOrganization($object, $jsonData, $connectorName = null, SymfonyStyle $io)
    {
        if ($connectorName !== null) {
            $object->setConnectorID(
                $connectorName,
                $this->getFieldValue($jsonData, 'uid', null,$io)
            );
        }
        $object
            ->setLabintel($this->getFieldValue($jsonData, 'labintel', null, $io))
            ->setShortName($this->getFieldValue($jsonData, 'shortname', null, $io))
            ->setCode($this->getFieldValue($jsonData, 'code', null, $io))
            ->setFullName($this->getFieldValue($jsonData, 'longname', null, $io))
            ->setPhone($this->getFieldValue($jsonData, 'phone', null, $io))
            ->setDescription($this->getFieldValue($jsonData, 'description', null, $io))
            ->setEmail($this->getFieldValue($jsonData, 'email', null, $io))
            ->setUrl($this->getFieldValue($jsonData, 'url', null, $io))
            ->setSiret($this->getFieldValue($jsonData, 'siret', null, $io))
            ->setType($this->getFieldValue($jsonData, 'type', null, $io))
            ->setTypeObj($this->getTypeObj($this->getFieldValue($jsonData, 'type', null, $io)))

            // Ajout de champs
            ->setDuns($this->getFieldValue($jsonData, 'duns', null, $io))
            ->setTvaintra($this->getFieldValue($jsonData, 'tvaintra', null, $io))
            ->setRnsr($this->getFieldValue($jsonData, 'rnsr', null, $io));

        if (property_exists($jsonData, 'address')) {
            $address = $jsonData->address;
            if (is_object($address)) {
                $object
                    ->setStreet1(property_exists($address, 'address1') ? $address->address1 : null)
                    ->setStreet2(property_exists($address, 'address2') ? $address->address2 : null)
                    ->setZipCode(property_exists($address, 'zipcode') ? $address->zipcode : null)
                    ->setCity(property_exists($address, 'city') ? $address->city : null)
                    ->setCountry(property_exists($address, 'country') ? $address->country : null)
                    ->setBp(property_exists($address, 'address3') ? $address->address3 : null);
            }
        }

        return $object;
    }

    protected function getFieldValue(
        $object,
        $fieldName,
        $defaultValue = null,
        SymfonyStyle $io
    ) {
        if (!property_exists($object, $fieldName)) {
            $io->writeln(sprintf("La clef '%s' est manquante dans la source",
                $fieldName));
        }

        return property_exists($object,
            $fieldName) ? $object->$fieldName : $defaultValue;
    }

    public function getOrganizationRepository(){
        return $this->getEntityManager()->getRepository(Organization::class);
    }

    public function getEntityManager(){
        return $this->getServiceManager()->get(EntityManager::class);
    }
}