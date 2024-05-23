<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Connector\ConnectorSpentSifacOCI;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\SpentService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarSpentSyncCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'spent:sync';

    protected function configure()
    {
        $this
            ->setDescription("Permet d'obtenir les dépenses")
            ->setHelp("")
            ->addArgument('pfi', InputArgument::OPTIONAL, "PFI à synchroniser");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Synchronisation des dépenses");

        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        $pfi = $input->getArgument('pfi');

        if ($pfi) {
            $io->writeln("PFI : $pfi");
        }
        else {
            $q = new Question("Synchroniser toutes les dépenses ?");
            $io->askQuestion($q);
            $io->error("Pas encore disponible");
            return Command::FAILURE;
        }

        try {
            $connectorConfig = $oscarConfig->getConfiguration('connectors.spent');

            $keysConfig = array_keys($connectorConfig);
            if (count($keysConfig) == 0) {
                $io->error("Pas de synchronisation des dépenses configuré");
                return Command::FAILURE;
            }
            elseif (count($keysConfig) > 1) {
                $io->error("Oscar ne prends en charge qu'une source de synchronisation pour les dépenses.");
                return Command::FAILURE;
            }
            else {
                $conf = $connectorConfig[$keysConfig[0]];
                $class = $conf['class'];
                $factory = new \ReflectionClass($class);

                /** @var ConnectorSpentSifacOCI $instance */
                $instance = $factory->newInstanceArgs(
                    [$this->getServicemanager()->get(SpentService::class), $conf['params']]
                );

                $result = $instance->sync($pfi, true);
                $io->write($result);
                return Command::SUCCESS;
            }
        } catch (\Exception $e) {
            $io->error("Impossible de synchroniser les dépenses : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}