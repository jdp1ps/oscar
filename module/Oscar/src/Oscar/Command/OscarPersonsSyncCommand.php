<?php

namespace Oscar\Command;

use Exception;
use Oscar\Connector\ConnectorRepport;
use Oscar\Service\ConnectorService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarPersonsSyncCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'persons:sync';

    protected function configure()
    {
        $this
            ->setDescription("Execute la synchronisation des personnes")
            ->addArgument("connectorname", InputArgument::REQUIRED, "Connector (rest)")
            ->addOption(
                'no-rebuild',
                'b',
                InputOption::VALUE_NONE,
                'Ignore la reconstruction de l\'index de recherche après la mise à jour'
            )
            ->addOption(
                'purge',
                'p',
                InputOption::VALUE_NONE,
                'Supprime les personnes d\'Oscar si elles ne sont plus proposées dans la source distante (et qu\'elles ne sont pas utilisées dans Oscar)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);

        $io->title("Synchronisation des personnes");

        $connectorName = $input->getArgument('connectorname');


        $io->section("Connector infos : ");
        $io->writeln("Connecteur : <bold>$connectorName</bold>");

        $purge = $input->getOption('purge');

        /** @var ConnectorService $connectorService */
        $connectorService = $this->getServicemanager()->get(ConnectorService::class);

        $connector = $connectorService->getConnector("person." . $connectorName);

        try {
            $connector->setOptionPurge($purge);

            /** @var ConnectorRepport $repport */
            $repport = $connector->execute();
            foreach ($repport->getRepportStates() as $type => $out) {
                $short = substr($type, 0, 3);
                $io->section("Opération " . strtoupper($type));
                foreach ($out as $line) {
                    $io->writeln("$short\t " . date('Y-m-d H:i:s', $line['time']) . " " . $line['message']);
                }
            }
        } catch (Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->section("Reconstruction de l'index de recherche : ");
        if (!$input->getOption('no-rebuild')) {
            /** @var PersonService $personService */
            $personService = $this->getServicemanager()->get(PersonService::class);

            try {
                $persons = $personService->getPersons();
                $personService->getSearchEngineStrategy()->rebuildIndex($persons);
                $io->success(sprintf('Index de recherche mis à jour avec %s personnes indexées', count($persons)));
            } catch (Exception $e) {
                $io->error($e->getMessage());
                return Command::FAILURE;
            }
        }
        else {
            $io->warning("Pas de reconstruction d'index");
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}