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

class OscarPersonsSyncCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'oscar:persons:sync';

    protected function configure()
    {
        $this
            ->setDescription("Execute la synchronisation des personnes")
            ->addArgument("connectorname", InputArgument::REQUIRED, "Connector (rest)")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Synchronisation des personnes");

        $connectorName = $input->getArgument('connectorname');

        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        $io->section("Connector infos : ");
        $io->writeln("Connecteur : <bold>$connectorName</bold>");

        /** @var ConnectorService $connectorService */
        $connectorService = $this->getServicemanager()->get(ConnectorService::class);

        $connector = $connectorService->getConnector("person.".$connectorName);

        try {
            $repport = $connector->execute();
            foreach ($repport->getRepportStates() as $type => $out) {
                $short = substr($type, 0, 3);
                $io->section( "Opération " . strtoupper($type));
                foreach ($out as $line) {
                    $io->writeln("$short\t " . date('Y-m-d H:i:s', $line['time']) . " " . $line['message'] );
                }
            }
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}