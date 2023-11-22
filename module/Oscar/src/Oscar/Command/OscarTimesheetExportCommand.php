<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Entity\Person;
use Oscar\Formatter\Timesheet\TimesheetActivityPeriodHtmlFormatter;
use Oscar\Formatter\Timesheet\TimesheetPersonPeriodToHtmlFormateur;
use Oscar\Service\PersonService;
use Oscar\Service\TimesheetService;
use Oscar\Utils\PeriodInfos;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarTimesheetExportCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:export';

    const ARG_ACTIVITY      = "activity";

    protected function configure()
    {
        $this
            ->setDescription("Exportation des feuilles de temps.")
            ->addOption('person', 'p', InputOption::VALUE_OPTIONAL, "identifiant du déclarant")
            ->addOption('period', 'd', InputOption::VALUE_REQUIRED, "Période (YYYY-MM)")
            ->addOption('activity', 'a', InputOption::VALUE_OPTIONAL, "N°Oscar")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) :int
    {
        $io = new SymfonyStyle($input, $output);


        /// OPTIONS and PARAMETERS
        $personLogin = $input->getOption('person');
        $period = $input->getOption('period');
        $activityNum = $input->getOption('activity');

        $person = null;
        $title = "";

        if( $period ){
            $periodInfo = PeriodInfos::getPeriodInfosObj($period);
        } else {
            $io->error("La période n'est pas spécifiée");
            return self::FAILURE;
        }

        if( $personLogin ){
            $person = $this->getPersonService()->getPersonByLdapLogin($personLogin);
            if( !$person ){
                $io->error("la personne '$personLogin' n'existe pas");
                return self::FAILURE;
            }
        }

        if( !$personLogin && !$activityNum ){
            $io->error("Précisiez l'activité et/ou la personne");
            return self::FAILURE;
        }

        try {


            $activity = $this->getTimesheetService()->getActivityService()->getActivityByOscarNum($activityNum);
            $datas = $this->getTimesheetService()->getSynthesisActivityPeriod($activity->getId(), $periodInfo->getPeriodCode());
            $formatter = new TimesheetActivityPeriodHtmlFormatter(
                $this->getTimesheetService()->getOscarConfigurationService()->getTimesheetTemplateActivityPeriod());
            $formatter->stream($datas);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Impossible de charger l'activité : " . $e->getMessage());
            return self::FAILURE;
        }



        if( $personLogin ){
            $person = $this->getPersonService()->getPersonByLdapLogin($personLogin);
            $title = "Feuille de temps pour '$person'";

            // Périodes de la personne
            $periods = $this->getTimesheetService()->getPeriodsPerson($person);
        }

        if( $person && $period ){

            if( !in_array($period, $periods) ){
                $io->error("'$person' n'est pas déclarant sur la période '". $periodInfo->getPeriodLabel()."'");
                return self::FAILURE;
            }
            $title .= " (Période '".$periodInfo->getPeriodLabel()."')";
            // $io->title($title);

            $datas = $this->getTimesheetService()->getTimesheetDatasPersonPeriod($person, $periodInfo->getPeriodCode());
            $formatter = new TimesheetPersonPeriodToHtmlFormateur();
            die($formatter->format($datas));

        }


        return self::SUCCESS;

        try {
            $activity = $this->getTimesheetService()->getActivityService()->getActivityById($activityId, true);
            $io->title("Validateurs pour $activity");

            $validators = $this->getTimesheetService()->getValidatorsPrj($activity);
            foreach ( $validators as $person ){
                $io->writeln("<info>1. Projet : </info> $person");
            }

            $validators = $this->getTimesheetService()->getValidatorsSci($activity);
            foreach ( $validators as $person ){
                $io->writeln("<info>2. Scientifique : </info> $person");
            }

            $validators = $this->getTimesheetService()->getValidatorsAdm($activity);
            foreach ( $validators as $person ){
                $io->writeln("<info>3. Administratif : </info> $person");
            }


        } catch (\Exception $e) {
            $io->error("Impossible de charger l'activité '$activityId' : " . $e->getMessage());
            return self::FAILURE;
        }

        return self::INVALID;
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
        $io = new SymfonyStyle($input, $output);
        $io->title("Lite des déclarants");
        try {
            $declarants = $this->getTimesheetService()->getDeclarers();
            $out = [];
            /** @var Person $declarer */
            foreach ($declarants['persons'] as $personId=>$datas) {
                $out[] = [$personId, $datas['displayname'], $datas['affectation'], count($datas['declarations'])];
            }
            $headers = ['ID', 'Déclarant', 'Affectation', 'Déclaration(s)'];
            $io->table($headers, $out);

            $io->comment("Entrez la commande '".self::getName()." <ID> [PERIOD]' pour afficher les détails");
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}