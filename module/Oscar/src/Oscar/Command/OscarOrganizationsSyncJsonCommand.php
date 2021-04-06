<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\ORM\EntityManager;
use Moment\Moment;
use Oscar\Connector\ConnectorOrganizationJSON;
use Oscar\Connector\GetJsonDataFromFileStrategy;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\Formatter\ConnectorRepportToPlainText;
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

class OscarOrganizationsSyncJsonCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'organizations:sync-json';

    protected function configure()
    {
        $this
            ->setDescription("Execute la synchronisation des organisations depuis une source JSON")
            ->addArgument("fichier", InputArgument::REQUIRED, "Fichier JSON")
            ->addOption('no-rebuild','b', InputOption::VALUE_NONE, 'Ignore la reconstruction de l\'index de recherche après la mise à jour')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Synchronisation des organisations (JSON)");

        $noRebuild = $input->getOption('no-rebuild');

        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        /** @var EntityManager $entityManager */
        $entityManager = $this->getServicemanager()->get(EntityManager::class);

        /** @var ConnectorService $connectorService */
        $connectorService = $this->getServicemanager()->get(ConnectorService::class);
        try {
            $fichier = $input->getArgument('fichier');

            if (!$fichier)
                die("Vous devez spécifier le chemin complet vers le fichier JSON");

            echo "Synchronisation depuis le fichier $fichier\n";
            $sourceJSONFile = new GetJsonDataFromFileStrategy($fichier);
            try {
                $datas = $sourceJSONFile->getAll();
            } catch (\Exception $e) {
                die("ERR : Impossible de charger les ogranizations depuis $fichier : " . $e->getMessage());
            }

            $connector = new ConnectorOrganizationJSON($datas,
                $entityManager,
                'json');

            $repport = $connector->syncAll();
            $connectorFormatter = new ConnectorRepportToPlainText();

            $connectorFormatter->format($repport);
        } catch (\Exception $e) {
            die("ERR : " . $e->getMessage());
        }

        $io->section("Reconstruction de l'index de recherche : ");
        if( !$noRebuild ){
            /** @var OrganizationService $organizationService */
            $organizationService = $this->getServicemanager()->get(OrganizationService::class);

            try {
                $organizations = $organizationService->getOrganizations();
                $organizationService->getSearchEngineStrategy()->rebuildIndex($organizations);
                $io->success(sprintf('Index de recherche mis à jour avec %s organisations indexées', count($organizations)));
            } catch ( \Exception $e ){
                $io->error($e->getMessage());
            }
        } else {
            $io->warning("Pas de reconstruction d'index");
        }
    }
}