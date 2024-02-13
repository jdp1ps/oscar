<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Moment\Moment;
use Oscar\Connector\ConnectorRepport;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationLdap;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationType;
use Oscar\Entity\Person;
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
use UnicaenApp\Mapper\Ldap\Structure;
use UnicaenApp\Entity\Ldap\Structure as LdapStructureModel;
use Zend\Ldap\Ldap;

class OscarLdapOrganizationsSyncCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'ldap:organizations:sync';
    private $configPath = null;
    private $configFile;
    private $configLdap = array(
        "type" => "organization_ldap",
        "label" => "Organization Ldap",
        "filtrage" => "&(objectClass=supannEntite)(supannTypeEntite={SUPANN}S*)(businessCategory=research),&(objectClass=supannEntite)(supannTypeEntite={SUPANN}S*)(businessCategory=administration)"
    );

    protected function configure()
    {
        $this
            ->setDescription("Synchronisation des organisations depuis LDAP")
        ;

        $this->configPath = realpath(__DIR__.'/../../') . "/../../../config/connectors/organization_ldap.yml";
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Synchronisation LDAP des organisations");

        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        /** @var OrganizationService $organisationService */
        $organisationService = $this->getServicemanager()->get(OrganizationService::class);

        try {
            $moduleOptions = $this->getServicemanager()->get('unicaen-app_module_options');
            $this->configFile = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPath));

            $configLdap = $moduleOptions->getLdap();
            $ldap = $configLdap['connection']['default']['params'];

            $dataStructureFromLdap = new OrganizationLdap();
            $dataStructureFromLdap->setConfig($configLdap);
            $dataStructureFromLdap->setLdap(new Ldap($ldap));

            try {
                $filtrage = $this->configFile['filtre_ldap'];
                $dataFiltrage = explode(",", $filtrage);
                $data = array();

                foreach($dataFiltrage as $filtre) {
                    $dataOrg = null;
                    $dataOrg = $dataStructureFromLdap->findOneByFilter($filtre);

                    foreach($dataOrg as $organization){
                        $dataProcess = array();

                        $dataProcess['uid'] = $organization["supannrefid"];
                        $dataProcess['name'] = $organization["description"];
                        $dataProcess['dateupdate'] = null;
                        $dataProcess['code'] = $organization["supanncodeentite"];
                        $dataProcess['shortname'] = $organization["ou"];
                        $dataProcess['longname'] = $organization["description"];
                        $dataProcess['phone'] = isset($organization["telephonenumber"]) ? $organization["telephonenumber"] : null;
                        $dataProcess['description'] = $organization["description"];
                        $dataProcess['email'] = "";
                        $dataProcess['siret'] = "";
                        $dataProcess['type'] = $organization["supanntypeentite"];
                        $dataProcess['url'] = isset($organization["labeleduri"]) ? $organization["labeleduri"] : null;
                        $dataProcess['duns'] = null;
                        $dataProcess['tvaintra'] = null;

                        $dataProcess['rnsr'] = "";
                        $dataProcess['labintel'] = "";

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
                                    $dataProcess['labintel'] = $refId;
                                }

                                if(str_contains($organization["supannrefid"], 'RNSR')){
                                    $dataProcess['rnsr'] = $refId;
                                }
                            }
                        }

                        $dataProcess['ldapsupanncodeentite'] = $organization["supanncodeentite"];

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

                        $data[] = (object) $dataProcess;
                    }
                }

            } catch (\Exception $e) {
                $io->writeln("Impossible de charger des données depuis  : " . $e->getMessage());
            }

            $this->syncAll($data, $this->getEntityManager()->getRepository(Organization::class), $io, false);
        } catch (\Exception $e ){
            $io->error($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    function syncAll($organizationsData, OrganizationRepository $repository, SymfonyStyle $io, $force)
    {
        try {
            $nbAjouts = 0;
            $nbMisaJour = 0;

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
                    $dateupdated = date('Y-m-d H:i:s');
                } else {
                    if( $data->dateupdated == null )
                        $data->dateupdated = "";
                    else
                        $dateupdated = $data->dateupdated;
                }
                if($organization->getDateUpdated() < new \DateTime($dateupdated) || $force == true ){

                    $organization = $this->hydrateWithDatas($organization, $data, 'ldap', $io);
                    if( property_exists($data, 'type') )
                        $organization->setTypeObj($repository->getTypeObjByLabel($data->name));
                    $repository->flush($organization);
                    if( $action == 'add' ){
                        $nbAjouts++;
                        $io->writeln(sprintf("%s a été ajouté.", $organization->log()));
                    } else {
                        $nbMisaJour++;
                        $io->writeln(sprintf("%s a été mis à jour.", $organization->log()));
                    }
                } else {
                    $io->writeln(sprintf("%s est à jour.", $organization->log()));
                }
            }
        } catch (\Exception $e ){
            $io->writeln($e->getMessage());
        }

        $io->writeln(sprintf("%s ajout(s) d'organisations.",$nbAjouts ));
        $io->writeln(sprintf("%s mise(s) à jour d'organisations.",$nbMisaJour ));
        $io->writeln("FIN du traitement...");

        return true;
    }

    function hydrateWithDatas($object, $jsonData, $connectorName = null, SymfonyStyle $io)
    {
        if ($connectorName !== null) {
            $object->setConnectorID(
                $connectorName,
                $this->getFieldValue($jsonData, 'code', null,$io)
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