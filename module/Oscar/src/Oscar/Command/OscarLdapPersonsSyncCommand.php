<?php
/**
 * Created by PhpStorm.
 * User: Sisomolida HING
 * Date: 14/02/2024
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\ORM\EntityManager;
use Oscar\Connector\DataExtractionStrategy\LdapExtractionStrategy;
use Oscar\Entity\Organization;
use Oscar\Entity\Person;
use Oscar\Entity\PersonLdap;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\Ldap\Ldap;

class OscarLdapPersonsSyncCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'ldap:persons:sync';

    private string $configPath = "";

    protected function configure(): void
    {
        $this
            ->setDescription("Synchronisation des personnes depuis LDAP")
        ;
        $this->configPath = realpath(__DIR__.'/../../') . "/../../../config/connectors/person_ldap.yml";
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->addOutputStyle($output);
        $io = new SymfonyStyle($input, $output);
        $io->title("Synchronisation LDAP des personnes");

        try {
            $moduleOptions = $this->getServicemanager()->get('unicaen-app_module_options');
            $configFile = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPath));

            $configLdap = $moduleOptions->getLdap();
            $ldap = $configLdap['connection']['default']['params'];
            $data = array();
            $nbModification = 0;

            try {
                $dataFiltered = $configFile["ldap_filter"];
                $extractorLdap = new LdapExtractionStrategy($this->getServicemanager());

                if(!is_array($dataFiltered)){
                    $dataFiltered = array($dataFiltered);
                }

                foreach($dataFiltered as $filter){
                    $io->writeln("Exécution d'un filtre : veuillez patienter (ce script peut prendre une dizaine de minutes ... )");

                    $connectorLdap = $extractorLdap->initiateLdapPerson($configLdap, $ldap);
                    $data = $connectorLdap->findAll($filter);
                    $personsData = array();

                    foreach($data as $person){
                        $personObj = $extractorLdap->parseLdapPerson($person);
                        $personsData[] = (object) $personObj;
                    }

                    $nbModification += count($personsData);
                    $extractorLdap->syncPersons(
                        $personsData,
                        $this->getEntityManager()->getRepository(Person::class),
                        $io
                    );
                }

            } catch (\Exception $e) {
                $io->error("Impossible de charger des données depuis : " . $e->getMessage());
            }

            if( !is_array($data) ){
                $io->error("LDAP n'a pas retourné un tableau de donnée");
            }

            $io->writeln("Ajout(s) ou mise(s) à jour : $nbModification personnes");
        } catch (\Exception $e ){
            $io->error($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function getEntityManager(){
        return $this->getServiceManager()->get(EntityManager::class);
    }

    public function getOrganizationRepository(){
        return $this->getEntityManager()->getRepository(Organization::class);
    }
}