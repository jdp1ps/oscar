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

class OscarTimesheetRecallCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:declarers-recall';

    const OPT_FORCE         = "force";
    const OPT_DECLARER      = "declarer";
    const OPT_PERIOD        = "period";

    protected function configure()
    {
        $this
            ->setDescription("Système de relance des déclarants")
            ->addOption(self::OPT_FORCE, 'f', InputOption::VALUE_NONE, "Forcer le mode non-interactif")
            ->addOption(self::OPT_DECLARER, 'd', InputOption::VALUE_REQUIRED, "Identifiant du déclarant")
            ->addOption(self::OPT_PERIOD, 'p', InputOption::VALUE_OPTIONAL, "Période");
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /// OPTIONS and PARAMETERS
        $declarerId = $input->getOption(self::OPT_DECLARER);
        $declarerPeriod = $input->getOption(self::OPT_PERIOD);

        if( !$declarerPeriod ){
            $today = date('Y-m-d');
            $time = strtotime($today);
            $final = date("Y-m-d", strtotime("-1 month", $time));
            $declarerPeriod = DateTimeUtils::getPeriodStrFromDateStr($final);
        }

        $this->declarerPeriod($input, $output, $declarerId, $declarerPeriod);

    }

    /**
     * @return TimesheetService
     */
    protected function getTimesheetService(){
        return $this->getServicemanager()->get(TimesheetService::class);
    }

    /**
     * @return PersonService
     */
    protected function getPersonService(){
        return $this->getServicemanager()->get(PersonService::class);
    }

    //public function declarerRecall

    public function declarerPeriod( InputInterface $input, OutputInterface $output, $declarerId, $period ){
        $this->getTimesheetService()->getPersonRecallDeclarationPeriod($declarerId, $period);
    }

    public function declarer( InputInterface $input, OutputInterface $output, $declarerId ){

        $io = new SymfonyStyle($input, $output);

        try {
            $declarer = $this->getPersonService()->getPerson($declarerId);

            $io->title("Système de relance pour $declarer");
            $periods = $this->getTimesheetService()->getPersonRecallDeclaration($declarer);

            $io->table(["Période", "Durée", "état"], $periods);

        } catch (\Exception $e) {
            $io->error('Impossible de charger le déclarant : ' . $e->getMessage());
            exit(0);
        }
    }

    public function declarersList( InputInterface $input, OutputInterface $output ){

    }
}