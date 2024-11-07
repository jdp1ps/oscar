<?php

namespace Oscar\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Service\ConnectorService;
use Oscar\Service\OrganizationService;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarOrganizationSyncOneCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'organization:syncone';

    protected function configure()
    {
        $this
            ->setDescription("Execute la synchronisation d'une organisation")
            ->addArgument("connectorname", InputArgument::REQUIRED, "Connector (rest)")
            ->addArgument('value', InputArgument::REQUIRED, 'Identifiant de l\'organisation dans la source de données distante (remote id)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forcer la mise à jour')
            ->addOption(
                'no-rebuild',
                'b',
                InputOption::VALUE_NONE,
                'Ignore la reconstruction de l\'index de recherche après la mise à jour'
            )->addOption(
                'purge',
                'p',
                InputOption::VALUE_NONE,
                'Déclenche la suppression des organisations qui sont retirées de la source'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        try {

            $this->addOutputStyle($output);

            $io = new SymfonyStyle($input, $output);
            $io->title("Synchronisation d'une organisation");

            $connectorName        = $input->getArgument("connectorname");
            $organizationRemoteID = $input->getArgument("value");
            $force                = $input->getOption('force');
            $noRebuild            = $input->getOption('no-rebuild');

            $io->section("Connector infos : ");
            $io->writeln("Connecteur : <bold>$connectorName</bold>, remote id : $organizationRemoteID");

            /** @var ConnectorService $connectorService */
            $connectorService = $this->getServicemanager()->get(ConnectorService::class);

            $connector = $connectorService->getConnector("organization." . $connectorName);
            $connector->setOptionPurge($input->getOption('purge'));

            /** @var OrganizationRepository $organizationRepository */
            $organizationRepository = $this->getServicemanager()->get(EntityManager::class)->getRepository(Organization::class);
            
            $organization = NULL;
            $organizationDateUpdated = NULL;
            try {
                $organization = $organizationRepository->getObjectByConnectorID($connectorName, $organizationRemoteID);
                $organizationDateUpdated = $organization->getDateUpdated();
            } catch (NonUniqueResultException $e) {
                $io->error("Plusieurs organisations dans la base de données organization d'Oscar ont ce remote id (" . $organizationRemoteID . ") pour ce connecteur (" . $connectorName . ")");

                return self::FAILURE;

            } catch (NoResultException $e) {
                $io->writeln("Cette organisation n'existe pas encore dans Oscar. Si elle est trouvée dans la source de données distante via le connecteur alors elle sera ajoutée.");
            }

            $organizationRemote = $connector->syncOrganization($organization, $organizationRemoteID, $force);

            if ($organization == NULL && $organizationRemote == NULL) {
                $io->warning(sprintf("L'organisation d'id '%s' n'est présente ni dans Oscar, ni dans les données du connecteur distant et n'a donc pas été synchronisée.", $organizationRemoteID));
            } else if ($organization != NULL && $organizationRemote == NULL && !$input->getOption('purge')) {
                $io->warning(sprintf("L'organisation d'id '%s' n'est pas présente dans les données du connecteur distant et n'a donc pas été synchronisée. Pour supprimer l'organisation dans la base de données locale d'Oscar, relancez la commande avec l'option --purge", $organizationRemoteID));
            } else if ($organization != NULL && $organizationRemote == NULL && $input->getOption('purge')) {
                $io->warning(sprintf("L'organisation d'id '%s' n'est pas présente dans les données du connecteur distant et a donc été supprimée de la base de données locale d'Oscar.", $organizationRemoteID));
            } else if ($organization == NULL && $organizationRemote != NULL) {
                $io->success(sprintf("L'organisation '%s' a été ajoutée.", $organizationRemote));
            } else if ($organizationDateUpdated >= $organizationRemote->getDateUpdated() && !$force) {
                $io->info(sprintf("L'organisation '%s' est déjà à jour : date de dernière modification (dateupdated) plus récent ou égale à celle de la donnée distante du connecteur. Pour forcer tout de même la synchronisation, relancez la commande avec l'option --force", $organization));
            } else {
                $io->success(sprintf("L'organisation '%s' a été synchronisée.", $organization));
            }

        } catch ( \Exception $e ){
            $io->error($e->getMessage());
            return self::FAILURE;
        }
        
        $io->section("Reconstruction de l'index de recherche : ");
        if (!$noRebuild) {
            /** @var OrganizationService $organizationService */
            $organizationService = $this->getServicemanager()->get(OrganizationService::class);

            try {
                $organizations = $organizationService->getOrganizations();
                $organizationService->getSearchEngineStrategy()->rebuildIndex($organizations);
                $io->success(
                    sprintf('Index de recherche mis à jour avec %s organisations indexées', count($organizations))
                );
            } catch (\Exception $e) {
                $io->error($e->getMessage());
                return self::FAILURE;
            }
        } else {
            $io->warning("Pas de reconstruction d'index");
        }

        return self::SUCCESS;
    }
}
