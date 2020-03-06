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
use Oscar\Entity\SpentLine;
use Oscar\Service\ConnectorService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\SpentService;
use Oscar\Service\TimesheetService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarSpentActivityListCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'spent:activitylist';

    protected function configure()
    {
        $this
            ->setDescription("Affiche la liste des dépenses des activités")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        /** @var SymfonyStyle $io */
        $io = new SymfonyStyle($input, $output);

        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        /** @var SpentService $spentService */
        $spentService = $this->getServicemanager()->get(SpentService::class);

        try {
            $datas = $spentService->getDatasActivitiesSpents();

            /*
            $headers = ['N°Pièce', 'Description', 'Montant', 'Date Comptable', 'Date Paiement', 'Année', 'N° Réf Pièce', 'IDs'];

            $io->table($headers, $out);
            */
//            $repport = $connector->execute();
//            foreach ($repport->getRepportStates() as $type => $out) {
//                $short = substr($type, 0, 3);
//                $io->section( "Opération " . strtoupper($type));
//                foreach ($out as $line) {
//                    $io->writeln("$short\t " . date('Y-m-d H:i:s', $line['time']) . " " . $line['message'] );
//                }
//            }
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}