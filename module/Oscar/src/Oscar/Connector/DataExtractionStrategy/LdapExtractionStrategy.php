<?php
/**
 * Created by PhpStorm.
 * User: Sisomolida HING
 * Date: 14/02/24
 * Time: 13:34
 */

namespace Oscar\Connector\DataExtractionStrategy;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Connector\ConnectorPersonHydrator;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationLdap;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationType;
use Oscar\Entity\Person;
use Oscar\Entity\PersonLdap;
use Zend\Ldap\Exception\LdapException;
use Zend\Ldap\Ldap;
use Zend\ServiceManager\ServiceManager;

class LdapExtractionStrategy
{
    private ConnectorPersonHydrator $hydratorPerson;
    private bool $purge = false;
    private string $configPathOrganization;
    private array $configFileOrganization;
    private array $mappingRolePerson = array(
        //ID 21 correspond au role "Directeur de laboratoire" en base de donnée
        "{UAI:0751717J:HARPEGE.FCSTR}530" => 21
    );
    private ServiceManager $serviceManager;

    private $arrayTypes = array(
        "association",
        "collectivite",
        "composante",
        "etablissement",
        "groupement",
        "unknown",
        "institution",
        "laboratoire",
        "plateau",
        "societe",
    );

    public function __construct(ServiceManager $sm)
    {
        $this->serviceManager = $sm;
        $this->configPathOrganization = realpath(__DIR__.'/../../') . "/../../../../config/connectors/organization_ldap.yml";
        $this->configFileOrganization = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPathOrganization));
    }

    /**
     * @throws LdapException
     */
    public function initiateLdapPerson($configLdap, $ldap): PersonLdap
    {
        $ldapConnector = new PersonLdap();
        $ldapConnector->setConfig($configLdap);
        $ldapConnector->setLdap(new Ldap($ldap));

        return $ldapConnector;
    }

    /**
     * @throws LdapException
     */
    public function initiateLdapOrganization($configLdap, $ldap): OrganizationLdap
    {
        $ldapConnector = new OrganizationLdap();
        $ldapConnector->setConfig($configLdap);
        $ldapConnector->setLdap(new Ldap($ldap));

        return $ldapConnector;
    }

    public function parseLdapPerson($person): array
    {
        $person['firstname'] = isset($person['givenname']) ? $person['givenname'] : "";
        $person['lastname'] = isset($person['sn']) ? $person['sn'] : "";
        $person['login'] = isset($person['uid']) ? $person['uid'] : "";
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

        return $person;
    }

    public function syncPersons($personsData, $personRepository, object &$logger): void
    {
        $this->writeLog($logger,count($personsData). " résultat(s) reçus vont être traité.");

        if( $this->purge ){
            $exist = $personRepository->getUidsConnector($this->getName());
            $this->writeLog($logger, sprintf(_("Il y'a %s personne(s) référencées dans Oscar pour le connecteur '%s'."), count($exist), $this->getName()));
        }

        try {

            foreach( $personsData as $personData ){

                if( ! property_exists($personData, 'uid') ){
                    $this->writeLog($logger,sprintf("Les donnèes %s n'ont pas d'UID.", print_r($personData, true)), "error");
                    continue;
                }

                try {
                    /** @var Person $personOscar */
                    $personOscar = $personRepository->getPersonByConnectorID('ldap', $personData->uid);
                    $action = "update";

                } catch( NoResultException $e ){
                    $personOscar = $personRepository->newPersistantPerson();
                    $action = "add";

                } catch( NonUniqueResultException $e ){
                    $this->writeLog($logger,sprintf("La personne avec l'ID %s est en double dans oscar.", $personData->uid), "error");
                    continue;
                }

                if( $personData->dateupdated == null
                    || $personOscar->getDateSyncLdap() == null
                    || $personOscar->getDateSyncLdap()->format('Y-m-d') < $personData->dateupdated)
                {
                    $personOscar = $this->getPersonHydrate()->hydratePerson($personOscar, $personData, 'ldap');

                    if( $personOscar == null ){
                        $this->writeLog($logger,"WTF $action", "error");
                    }

                    if(isset($personData->supannroleentite)){
                        $rolesPerson = $personData->supannroleentite;
                        $organizationRepository = $this->getOrganizationRepository();

                        if(is_array($rolesPerson)){
                            foreach($rolesPerson as $role){
                                $substringRole = substr($role, 1, strlen($role)-2);
                                $explodeRole = explode("][",$substringRole);
                                $exactRole = substr($explodeRole[0],5,strlen($explodeRole[0]));
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
                    $personRepository->flush($personOscar);

                    if( $action == 'add' ){
                        $this->writeLog($logger,sprintf("%s a été ajouté.", $personOscar->log()));
                    } else {
                        $this->writeLog($logger,sprintf("%s a été mis à jour.", $personOscar->log()));
                    }
                } else {
                    $this->writeLog($logger,sprintf("%s est à jour.", $personOscar->log()));
                }
            }

            $nbPersons = count($personsData);
            $this->writeLog($logger,"$nbPersons personnes ont été ajouté(s) ou mise(s) à jour");

        } catch (\Exception $e ){
            $this->writeLog($logger,"Impossible de synchroniser les personnes : " . $e->getMessage(), "error");
        }

        $personRepository->flush(null);
    }

    public function parseOrganizationLdap($organization): array
    {
        $dataProcess = array();

        $dataProcess['uid'] = isset($organization["supannrefid"])? $organization["supannrefid"] : null;
        $dataProcess['name'] = isset($organization["description"]) ? $organization["description"] : null;
        $dataProcess['dateupdate'] = null;
        $dataProcess['code'] = isset($organization["supanncodeentite"]) ? $organization["supanncodeentite"] : null;
        $dataProcess['shortname'] = isset($organization["ou"]) ? $organization["ou"] : null;
        $dataProcess['longname'] = isset($organization["description"]) ? $organization["description"] : null;
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
                    $dataProcess['labintel'] = $organization["supannrefid"];
                }

                if(str_contains($organization["supannrefid"], 'RNSR')){
                    $dataProcess['rnsr'] = $organization["supannrefid"];
                }
            }
        }

        $dataProcess['ldapsupanncodeentite'] = isset($organization["supanncodeentite"]) ? $organization["supanncodeentite"] : null;

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

        return $dataProcess;
    }

    public function syncAllOrganizations($organizationsData, OrganizationRepository $repository, object $io): bool
    {
        try {
            $nbAdds = 0;
            $nbUpdates = 0;

            foreach( $organizationsData as $data ){
                try {
                    $iud = $data->code;
                    $organization = $repository->getObjectByConnectorID('ldap', $iud);
                    $action = "update";
                } catch( NoResultException $e ){
                    $organization = $repository->newPersistantObject();
                    $action = "add";
                }

                $dateUpdated = null;

                if( !property_exists($data, 'dateupdated') ){
                    $dateUpdated = date('Y-m-d H:i:s');
                } else {
                    if( $data->dateupdated == null ) {
                        $data->dateupdated = "";
                    } else {
                        $dateUpdated = $data->dateupdated;
                    }
                }
                if($organization->getDateUpdated() < new \DateTime($dateUpdated)){

                    $organization = $this->hydrateOrganization($organization, $data, $io,  'ldap');
                    if(property_exists($data, 'type')) {
                        $organization->setTypeObj($repository->getTypeObjByLabel($data->type));
                    }

                    $repository->flush($organization);

                    if( $action == 'add' ){
                        $nbAdds++;
                        $this->writeLog($io, sprintf("%s a été ajouté.", $organization->log()));
                    } else {
                        $nbUpdates++;
                        $this->writeLog($io, sprintf("%s a été mis à jour.", $organization->log()));
                    }
                } else {
                    $this->writeLog($io, sprintf("%s est à jour.", $organization->log()));
                }
            }
        } catch (\Exception $e ){
            $this->writeLog($io, $e->getMessage());
        }

        $this->writeLog($io, sprintf("%s ajout(s) d'organisations.",$nbAdds ));
        $this->writeLog($io, sprintf("%s mise(s) à jour d'organisations.",$nbUpdates ));
        $this->writeLog($io, "FIN du traitement...");

        return true;
    }

    function hydrateOrganization($object, $jsonData, object $logger, $connectorName = null)
    {
        if ($connectorName !== null) {
            $object->setConnectorID(
                $connectorName,
                $this->getFieldValue($jsonData, 'code', $logger, null)
            );
        }
        $object
            ->setDateUpdated(new \DateTime($this->getFieldValue($jsonData, 'dateupdate', $logger, null)))
            ->setLabintel($this->getFieldValue($jsonData, 'labintel', $logger, null))
            ->setShortName($this->getFieldValue($jsonData, 'shortname', $logger, null))
            ->setCode($this->getFieldValue($jsonData, 'code', $logger, null))
            ->setFullName($this->getFieldValue($jsonData, 'longname', $logger, null))
            ->setPhone($this->getFieldValue($jsonData, 'phone', $logger, null))
            ->setDescription($this->getFieldValue($jsonData, 'description', $logger, null))
            ->setEmail($this->getFieldValue($jsonData, 'email', $logger, null))
            ->setUrl($this->getFieldValue($jsonData, 'url', $logger, null))
            ->setSiret($this->getFieldValue($jsonData, 'siret', $logger, null))
            ->setType($this->getFieldValue($jsonData, 'type', $logger, null))
            ->setTypeObj($this->getTypeObj($this->getFieldValue($jsonData, 'type', $logger, null)))

            // Ajout de champs
            ->setDuns($this->getFieldValue($jsonData, 'duns', $logger, null))
            ->setTvaintra($this->getFieldValue($jsonData, 'tvaintra', $logger, null))
            ->setRnsr($this->getFieldValue($jsonData, 'rnsr', $logger, null));

        if(property_exists($jsonData, 'address') && is_object($jsonData->address)){
            $address = $jsonData->address;

            $object
                ->setStreet1(property_exists($address, 'address1') ? $address->address1 : null)
                ->setStreet2(property_exists($address, 'address2') ? $address->address2 : null)
                ->setZipCode(property_exists($address, 'zipcode') ? $address->zipcode : null)
                ->setCountry(property_exists($address, 'country') ? $address->country : null)
                ->setCity(property_exists($address, 'city') ? $address->city : null)
                ->setBp(property_exists($address, 'address3') ? $address->address3 : null);

        }

        return $object;
    }

    protected function getFieldValue(
        $object,
        $fieldName,
        object $logger,
        $defaultValue = null
    ) {
        if (!property_exists($object, $fieldName)) {
            $this->writeLog($logger, sprintf("La clef '%s' est manquante dans la source",
                $fieldName));
        }

        return property_exists($object,
            $fieldName) ? $object->$fieldName : $defaultValue;
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

    private function writeLog($objectLog, $message, $typeLog = "write"): void
    {
        if(get_class($objectLog) == "Symfony\Component\Console\Style\SymfonyStyle"){
            if($typeLog == "write"){
                $objectLog->writeln($message);
            }

            if($typeLog == "error"){
                $objectLog->error($message);
            }
        }

        if(get_class($objectLog) == "Oscar\Connector\ConnectorRepport"){
            if($typeLog == "write"){
                $objectLog->addnotice($message);
            }

            if($typeLog == "error"){
                $objectLog->addwarning($message);
            }
        }
    }

    function verifyTypes($typeSupann){

        foreach($this->arrayTypes as $typesCode){
            if($this->configFileOrganization[$typesCode."_array"] != ""){
                $explodeTypes = explode(",", $this->configFileOrganization[$typesCode."_array"]);

                foreach($explodeTypes as $configFileOrganization){
                    if($configFileOrganization == $typeSupann){
                        return $this->configFileOrganization[$typesCode."_id"];
                    }
                }
            }
        }

        return $this->configFileOrganization["unknown_id"];
    }

    public function getOrganizationRepository(){
        return $this->getEntityManager()->getRepository(Organization::class);
    }

    public function getPersonHydrate(): ConnectorPersonHydrator
    {
        $this->hydratorPerson = new ConnectorPersonHydrator(
            $this->getEntityManager()
        );
        $this->hydratorPerson->setPurge($this->purge);

        return $this->hydratorPerson;
    }

    private function getServiceManager(): ServiceManager
    {
        return $this->serviceManager;
    }

    public function getEntityManager(){
        return $this->getServiceManager()->get(EntityManager::class);
    }
}