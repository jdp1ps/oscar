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
use Oscar\Service\TimesheetService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarTimesheetDeclarersListCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:declarers';

    protected function configure()
    {
        $this
            ->setDescription("Affiche la liste des déclarants");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Déclarants");


        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServicemanager()->get(TimesheetService::class);

        try {
            $declarants = $timesheetService->getDeclarers();
            $out = [];
            /** @var Person $declarer */
            foreach ($declarants['persons'] as $personId=>$datas) {
                var_dump($datas);
                $out[] = [$personId, $datas['displayname']];
            }
            $headers = ['ID', 'Déclarant'];

            $io->table($headers, $out);
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