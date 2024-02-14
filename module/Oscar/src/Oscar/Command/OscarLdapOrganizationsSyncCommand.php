<?php
/**
 * Created by PhpStorm.
 * User: Sisomolida HING
 * Date: 14/02/24
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\ORM\EntityManager;
use Oscar\Connector\DataExtractionStrategy\LdapExtractionStrategy;
use Oscar\Entity\Organization;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarLdapOrganizationsSyncCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'ldap:organizations:sync';
    private string $configPath;

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
        $io = new SymfonyStyle($input, $output);
        $io->title("Synchronisation LDAP des organisations");

        try {
            $moduleOptions = $this->getServicemanager()->get('unicaen-app_module_options');
            $configFile = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->configPath));

            $configLdap = $moduleOptions->getLdap();
            $ldap = $configLdap['connection']['default']['params'];

            $extractorLdap = new LdapExtractionStrategy($this->getServicemanager());
            $connectorLdap = $extractorLdap->initiateLdapOrganization($configLdap, $ldap);

            try {
                $dataFiltered = $configFile['ldap_filter'];
                $data = array();

                foreach($dataFiltered as $filter) {
                    $dataOrg = null;
                    $dataOrg = $connectorLdap->findOneByFilter($filter);

                    foreach($dataOrg as $organization){
                        $dataProcess = $extractorLdap->parseOrganizationLdap($organization);
                        $data[] = (object) $dataProcess;
                    }
                }

            } catch (\Exception $e) {
                $io->writeln("Impossible de charger des données depuis  : " . $e->getMessage());
            }

            $extractorLdap->syncAllOrganizations($data, $this->getEntityManager()->getRepository(Organization::class), $io);
        } catch (\Exception $e ){
            $io->error($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function getOrganizationRepository(){
        return $this->getEntityManager()->getRepository(Organization::class);
    }

    public function getEntityManager(){
        return $this->getServiceManager()->get(EntityManager::class);
    }
}