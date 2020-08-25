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

class OscarOrganizationsSearchRebuildCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'organizations:search-rebuild';

    protected function configure()
    {
        $this
            ->setDescription("Reconstruction de l'index de recherche des organisations")
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
            $organizations = $organisationService->getOrganizations();
            $organisationService->getSearchEngineStrategy()->rebuildIndex($organizations);
            $io->success(sprintf('Index de recherche mis à jour avec %s organisations indexées', count($organizations)));

        } catch (\Exception $e ){
            $io->error($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}