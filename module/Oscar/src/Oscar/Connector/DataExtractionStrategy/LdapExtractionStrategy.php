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
use Oscar\Entity\Role;
use Psr\Container\NotFoundExceptionInterface;
use Zend\Ldap\Exception\LdapException;
use Zend\Ldap\Ldap;
use Zend\ServiceManager\ServiceManager;

class LdapExtractionStrategy
{
    private ConnectorPersonHydrator $hydratorPerson;
    private bool $purge = false;
    private string $configPathOrganization;
    private string $configPathPerson;
    private array $configFileOrganization;
    private array $configFilePerson;
    private ServiceManager $serviceManager;
    private $dateUpdated;
    private int $nbAdds = 0;
    private int $nbUpdates = 0;

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
        $this->configPathOrganization = realpath(__DIR__)
            . "/../../../../../../config/connectors/organization_ldap.yml";
        $this->configFileOrganization =
            \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPathOrganization));

        $this->configPathPerson = realpath(__DIR__)
            . "/../../../../../../config/connectors/person_ldap.yml";
        $this->configFilePerson =
            \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPathPerson));
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
        $person['codeHarpege'] = isset($person['supannentiteaffectationprincipale'])?
            $person['supannentiteaffectationprincipale'] : "" ;

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
        $person['projectAffectations'] = isset($person['edupersonaffiliation']) ?
            $person['edupersonaffiliation'] : null;
        $person['ldapsitelocation'] = isset($person['buildingName']) ? $person['buildingName']: null;

        if(isset($person["supannroleentite"])){
            $person['supannroleentite'] = $person["supannroleentite"];
        }

        $person = array_merge($person, $this->hydrateAffectation($person));

        $person['activities'] = null;
        $person['ladapLogin'] = isset($person['supannaliaslogin']) ? $person['supannaliaslogin'] : null;
        $person['dateupdated'] = null;

        return $person;
    }

    public function hydrateAffectation($dataAffectation): array
    {
        $affectationArray = array();

        if(isset($dataAffectation['supannentiteaffectation']) && is_array($dataAffectation['supannentiteaffectation'])){
            $nbAffectation = count($dataAffectation['supannentiteaffectation']);
            $nbTmp = 0;
            $affectationArray['affectation'] = "";
            $affectationArray['organizations'] = array();

            foreach($dataAffectation['supannentiteaffectation'] as $affectation){
                $affectationArray['affectation'] .= $affectation;
                $nbTmp++;

                if($nbTmp < $nbAffectation){
                    $affectationArray['affectation'] .= ',';
                }
            }
        } else {
            if(isset($dataAffectation['supannentiteaffectation'])) {
                $affectationArray['affectation'] = $dataAffectation['supannentiteaffectation'];
            }
        }

        return $affectationArray;
    }

    public function syncPersons($personsData, $personRepository, object &$logger): void
    {
        $this->writeLog($logger,count($personsData). " résultat(s) reçus vont être traité.");

        try {
            foreach( $personsData as $personData ){

                if( ! property_exists($personData, 'uid') ){
                    $this->writeLog($logger,sprintf("Les donnèes %s n'ont pas d'UID.",
                        print_r($personData, true)), "error");
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
                    $this->writeLog($logger,sprintf("La personne avec l'ID %s est en double dans oscar.",
                        $personData->uid), "error");
                    continue;
                }

                if( $personData->dateupdated == null
                    || $personOscar->getDateSyncLdap() == null
                    || $personOscar->getDateSyncLdap()->format('Y-m-d') < $personData->dateupdated)
                {
                    $personOscar = $this->getPersonHydrate()->hydratePerson($personOscar, $personData, 'ldap');
                    $this->hydrateRolePerson($personData, $personOscar);

                    $personRepository->flush($personOscar);

                    $this->writePersonLog($action, $logger, $personOscar);
                } else {
                    $this->writeLog($logger,sprintf("%s est à jour.", $personOscar->log()));
                }
            }
            $this->writeLog($logger,count($personsData)." personnes ont été ajouté(s) ou mise(s) à jour");

        } catch (\Exception $e ){
            $this->writeLog($logger,"Impossible de synchroniser les personnes : " . $e->getMessage(), "error");
        }

        $personRepository->flush(null);
    }

    public function writePersonLog($action, $logger, $personOscar): void
    {
        if( $action == 'add' ){
            $this->writeLog($logger,sprintf("%s a été ajouté.", $personOscar->log()));
        } else {
            $this->writeLog($logger,sprintf("%s a été mis à jour.", $personOscar->log()));
        }
    }

    public function hydrateRolePerson($personData, $personOscar): void
    {
        $organizationRepository = $this->serviceManager->get(EntityManager::class)->getRepository(
            Organization::class
        );

        if(isset($personData->supannroleentite)){
            $rolesPerson = $personData->supannroleentite;
            $ldapRoleStrategy = new LdapRoleStrategy($this->serviceManager);
            $deltaRolesPerson =
                $ldapRoleStrategy->compareRolesPerson(
                    $this->configFilePerson, $rolesPerson, $organizationRepository, $personOscar);

            if($deltaRolesPerson != null) {
                if (is_array($deltaRolesPerson)) {
                    foreach ($deltaRolesPerson as $role) {
                        $this->parseRolesPerson($role, $organizationRepository, $personOscar);
                    }
                } else {
                    $this->parseRolesPerson($deltaRolesPerson, $organizationRepository, $personOscar);
                }
            }
        }
    }

    public function parseOrganizationLdap($organization): array
    {
        $dataProcess = array();

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

        $dataProcess = array_merge($dataProcess, $this->hydrateRefId($organization));

        $dataProcess['ldapsupanncodeentite'] = isset($organization["supanncodeentite"]) ?
            $organization["supanncodeentite"] : null;

        $dataProcess = array_merge($dataProcess, $this->hydrateAddress($organization));


        return $dataProcess;
    }

    public function hydrateRefId($organization): array{
        $dataProcess = array();

        if(is_array($organization["supannrefid"])){
            foreach($organization["supannrefid"] as $refId){
                if(str_contains($refId, 'CNRS')){
                    $dataProcess['labintel'] = $refId;
                }
                if(str_contains($refId, 'RNSR')){
                    $dataProcess['rnsr'] = $refId;
                }
            }

        } elseif(isset($organization["supannrefid"])) {
            if(str_contains($organization["supannrefid"], 'CNRS')){
                $dataProcess['labintel'] = $organization["supannrefid"];
            }
            if(str_contains($organization["supannrefid"], 'RNSR')){
                $dataProcess['rnsr'] = $organization["supannrefid"];
            }
        }
        return $dataProcess;
    }

    public function hydrateAddress($organization): array
    {
        $dataProcess = array();

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
            foreach( $organizationsData as $data ){
                try {
                    $iud = $data->code;
                    $organization = $repository->getObjectByConnectorID('ldap', $iud);
                    $action = "update";
                } catch( NoResultException $e ){
                    $organization = $repository->newPersistantObject();
                    $action = "add";
                }

                if( !property_exists($data, 'dateupdated') ){
                    $this->dateUpdated = date('Y-m-d H:i:s');
                } else {
                    if( $data->dateupdated == null ) {
                        $data->dateupdated = "";
                    } else {
                        $this->dateUpdated = $data->dateupdated;
                    }
                }
                if($organization->getDateUpdated() < new \DateTime($this->dateUpdated)){
                    $organization = $this->hydrateOrganization($organization, $data,
                        $repository, $io,  'ldap');

                    $repository->flush($organization);
                    $this->writeOrganizationLog($action, $io, $organization);
                } else {
                    $this->writeLog($io, sprintf("%s est à jour.", $organization->log()));
                }
            }
        } catch (\Exception $e ){
            $this->writeLog($io, $e->getMessage());
        }

        $this->writeLog($io, sprintf("%s ajout(s) d'organisations.",$this->nbAdds ));
        $this->writeLog($io, sprintf("%s mise(s) à jour d'organisations.",$this->nbUpdates ));
        $this->writeLog($io, "FIN du traitement...");

        return true;
    }

    public function writeOrganizationLog($action, $io, $organization): void{
        if( $action == 'add' ){
            $this->nbAdds++;
            $this->writeLog($io, sprintf("%s a été ajouté.", $organization->log()));
        } else {
            $this->nbUpdates++;
            $this->writeLog($io, sprintf("%s a été mis à jour.", $organization->log()));
        }
    }

    public function hydrateOrganization($object, $orgData, $repository, object $logger = null, $connectorName = null)
    {
        if ($connectorName !== null) {
            $object->setConnectorID(
                $connectorName,
                $this->getFieldValue($orgData, 'code', $logger, null)
            );
        }
        $object
            ->setDateUpdated(new \DateTime($this->getFieldValue($orgData, 'dateupdate', $logger, null)))
            ->setLabintel($this->getFieldValue($orgData, 'labintel', $logger, null))
            ->setShortName($this->getFieldValue($orgData, 'shortname', $logger, null))
            ->setCode($this->getFieldValue($orgData, 'code', $logger, null))
            ->setFullName($this->getFieldValue($orgData, 'longname', $logger, null))
            ->setPhone($this->getFieldValue($orgData, 'phone', $logger, null))
            ->setDescription($this->getFieldValue($orgData, 'description', $logger, null))
            ->setEmail($this->getFieldValue($orgData, 'email', $logger, null))
            ->setUrl($this->getFieldValue($orgData, 'url', $logger, null))
            ->setSiret($this->getFieldValue($orgData, 'siret', $logger, null))
            ->setType($this->getFieldValue($orgData, 'type', $logger, null))
            ->setTypeObj($this->getTypeObj($this->getFieldValue($orgData, 'type', $logger, null)))

            // Ajout de champs
            ->setDuns($this->getFieldValue($orgData, 'duns', $logger, null))
            ->setTvaintra($this->getFieldValue($orgData, 'tvaintra', $logger, null))
            ->setRnsr($this->getFieldValue($orgData, 'rnsr', $logger, null));

        if(property_exists($orgData, 'address') && is_object($orgData->address)){
            $address = $orgData->address;

            $object
                ->setStreet1(property_exists($address, 'address1') ? $address->address1 : null)
                ->setStreet2(property_exists($address, 'address2') ? $address->address2 : null)
                ->setZipCode(property_exists($address, 'zipcode') ? $address->zipcode : null)
                ->setCountry(property_exists($address, 'country') ? $address->country : null)
                ->setCity(property_exists($address, 'city') ? $address->city : null);

        }
        if(property_exists($orgData, 'type')) {
            $object->setTypeObj($repository->getTypeObjByLabel($orgData->type));
        }

        return $object;
    }

    public function getFieldValue(
        $object,
        $fieldName,
        object $logger = null,
        $defaultValue = null
    ) {
        if (!property_exists($object, $fieldName)) {
            if($logger) {
                $this->writeLog($logger, sprintf("La clef '%s' est manquante dans la source",
                    $fieldName));
            }
            return false;
        }

        return property_exists($object,
            $fieldName) ? $object->$fieldName : $defaultValue;
    }

    protected function getTypeObj( string $typeLabel ) :?OrganizationType
    {
        try {
            $types = $this->serviceManager
                ->get(EntityManager::class)->getRepository(OrganizationType::class)->findAll();
            $allTypes = [];

            /** @var OrganizationType $organizationType */
            foreach ($types as $organizationType) {
                $allTypes[$organizationType->getLabel()] = $organizationType;
            }

            if (is_array($allTypes) && array_key_exists($typeLabel, $allTypes)) {
                return $allTypes[$typeLabel];
            }
        } catch(\Exception|NotFoundExceptionInterface $e){
            return null;
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

    public function verifyTypes($typeSupann){

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

    public function getPersonHydrate(): ConnectorPersonHydrator
    {
        $this->hydratorPerson = new ConnectorPersonHydrator(
            $this->serviceManager->get(EntityManager::class)
        );
        $this->hydratorPerson->setPurge($this->purge);

        return $this->hydratorPerson;
    }

    /**
     * @param $role
     * @param $organizationRepository
     * @param $personOscar
     * @return void
     */
    public function parseRolesPerson($role, $organizationRepository, $personOscar): void
    {
        $substringRole = substr($role, 1, strlen($role) - 2);
        $explodeRole = explode("][", $substringRole);
        $exactRole = substr($explodeRole[0], 5, strlen($explodeRole[0]));
        $exactCode = substr($explodeRole[2], 5, strlen($explodeRole[2]));
        $countRole = count($this->configFilePerson["mapping_role_person"]);
        $roleRepository = $this->serviceManager->get(EntityManager::class)->getRepository(
            Role::class
        );
        $dataOrgPer =
            $organizationRepository->getOrganisationPersonByPersonNullResult($personOscar);

        for($i=0;$i<$countRole;$i++) {
            if (array_key_exists($exactRole, $this->configFilePerson["mapping_role_person"][$i])) {
                $dataOrg = $organizationRepository->getOrganisationByCodeNullResult($exactCode);
                $idRole = $roleRepository->getRoleByRoleId(
                    $this->configFilePerson["mapping_role_person"][$i][$exactRole]
                )->getId();

                if ($dataOrg != null) {
                    if ($dataOrgPer == null) {
                        $dataOrgPer = new OrganizationPerson();
                    }

                    $organizationRepository->saveOrganizationPerson(
                        $dataOrgPer,
                        $personOscar,
                        $dataOrg,
                        $idRole
                    );
                }
            }
        }
    }
}
