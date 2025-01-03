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

class OscarOrganizationsSyncCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'organizations:sync';

    protected function configure()
    {
        $this
            ->setDescription("Execute la synchronisation des organisations")
            ->addArgument("connectorname", InputArgument::REQUIRED, "Connector (rest)")
            ->addOption('fullrepport', 's', InputOption::VALUE_NONE, "Rapport complet")
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
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);
        $io->title("Synchronisation des organisations");

        $connectorName = $input->getArgument('connectorname');
        $noRebuild = $input->getOption('no-rebuild');
        $force = $input->getOption('force');
        $fullRepport = $input->getOption('fullrepport');

        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        $io->section("Connector infos : ");
        $io->writeln("Connecteur : <bold>$connectorName</bold>");

        /** @var ConnectorService $connectorService */
        $connectorService = $this->getServicemanager()->get(ConnectorService::class);

        $connector = $connectorService->getConnector("organization." . $connectorName);
        $connector->setOptionPurge($input->getOption('purge'));

        try {
            $repport = $connector->execute($force);
            foreach ($repport->getRepportStates() as $type => $out) {
                $short = substr($type, 0, 3);
                $io->section("Opération " . strtoupper($type));
                if( $fullRepport === false && !in_array($type, ['errors', 'warnings']) ){
                    $io->writeln("Nbr d'opération : " . count($out));
                } else {
                    foreach ($out as $line) {
                        $io->writeln("$short\t " . date('Y-m-d H:i:s', $line['time']) . " " . $line['message']);
                    }
                }
            }
        } catch (\Exception $e) {
            $noRebuild = true;
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
            }
        } else {
            $io->warning("Pas de reconstruction d'index");
        }
        return self::SUCCESS;
    }
}