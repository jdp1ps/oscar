<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Moment\Moment;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Organization;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
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

class OscarLdapOrganizationsSearchCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'ldap:organizations:search';

    protected function configure()
    {
        $this
            ->setDescription("Recherche LDAP dans les organisations")
            ->addArgument("search", InputArgument::REQUIRED, "Expression à rechercher")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Recherche dans les organisations");

        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        /** @var OrganizationService $organisationService */
        $organisationService = $this->getServicemanager()->get(OrganizationService::class);

        try {
            $moduleOptions = $this->getServicemanager()->get('unicaen-app_module_options');

            $configLdap = $moduleOptions->getLdap();
            $ldap = $configLdap['connection']['default']['params'];
            $search = $input->getArgument('search');
            $dataStructureFromLdap = new Structure();
            $dataStructureFromLdap->setConfig($configLdap);
            $dataStructureFromLdap->setLdap(new Ldap($ldap));


            //$organisations = $organisationService->search($search);
            $organisations = $dataStructureFromLdap->findAllPathByCodeStructure($search);

            /** @var Organization $organisation */

            if(is_string($organisations)){
                $io->writeln(sprintf('- <bold>[%s]</bold>', $organisations));
            } else {
                foreach ($organisations as $organisation) {
                    $io->writeln(sprintf('- <bold>[%s]</bold> [%s] %s (%s)',
                        $organisation->getId(),
                        $organisation->getShortName(),
                        $organisation->getFullName(),
                        $organisation->getCode()));
                }
            }
        } catch (\Exception $e ){
            $io->error($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}