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
use Oscar\Entity\RecallDeclaration;
use Oscar\Entity\Role;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Service\ConnectorService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\TimesheetService;
use Oscar\Utils\DateTimeUtils;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\Validator\Date;

class OscarTimesheetRecallsCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:recalls';

    const OPT_DECLARER      = "declarer";
    const OPT_PERIOD        = "period";

    protected function configure()
    {
        $this
            ->setDescription("Afficher les relances")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);
        $io = new SymfonyStyle($input, $output);

        $recalls = $this->getTimesheetService()->getRecalls();

        $headers = ["ID", "Date", "Depuis", "Déclarants", "Période"];
        $rows = [];

        $moment = new Moment();

        /** @var RecallDeclaration $recall */
        foreach ($recalls as $recall) {
            $moment = new Moment($recall->getLastSend()->getTimestamp());
            $row = [
                $recall->getId(),
                $recall->getLastSend()->format('Y-m-d H:i:s'),
                $moment->fromNow()->getRelative(),
                $recall->getPerson()->__toString(),
                sprintf("%s %s", $recall->getPeriodMonth(), $recall->getPeriodYear())
            ];
            $rows[] = $row;
        }
        $io->table($headers, $rows);
        /// OPTIONS and PARAMETERS

    }

    /**
     * @return TimesheetService
     */
    protected function getTimesheetService(){
        return $this->getServicemanager()->get(TimesheetService::class);
    }
}