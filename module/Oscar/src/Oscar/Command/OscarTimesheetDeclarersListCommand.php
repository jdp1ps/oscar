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

class OscarTimesheetDeclarersListCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:declarers';
    const ARG_DECLARANT = "declarant";

    protected function configure()
    {
        $this
            ->setDescription("Affiche la liste des déclarants")
            ->addArgument(self::ARG_DECLARANT, InputArgument::OPTIONAL, "Identifiant du déclarant", null)
    //        ->addOption('period', 'p', InputOption::VALUE_OPTIONAL, "Période sous la forme ANNEE-MOIS")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        /** @var PersonService $personService */
        $personService = $this->getServicemanager()->get(PersonService::class);

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServicemanager()->get(TimesheetService::class);

        $io = new SymfonyStyle($input, $output);

        // détails
        $declarantId = $input->getArgument(self::ARG_DECLARANT);

        if( $declarantId ){
            try {
                /** @var Person $person */
                $person = $personService->getPerson($declarantId);
            } catch (\Exception $e) {
                $io->error($e->getMessage());
                exit(1);
            }

            $io->title("Déclarant <bold>$person</bold>");
            $headers = ['Projet', 'Lot', 'Début', 'Fin', 'Intitulé'];
            $lines = [];
            $start = null;
            $end = null;

            $io->section("Lots identifiés");

            /** @var WorkPackagePerson $workPackagePerson */
            foreach ($person->getWorkPackages() as $workPackagePerson) {
                $workPackage = $workPackagePerson->getWorkPackage();
                $lines[] = [
                  $workPackage->getActivity()->getAcronym(),
                  $workPackage->getCode(),
                  $workPackage->getActivity()->getDateStart()->format('D j F Y'),
                  $workPackage->getActivity()->getDateEnd()->format('D j F Y'),
                  $workPackage->getLabel(),
                ];

                $s = $workPackage->getActivity()->getDateStart()->getTimestamp();
                $e = $workPackage->getActivity()->getDateEnd()->getTimestamp();

                if($start == null || $start > $s)$start = $s;
                if( $end == null || $end < $e ) $end = $e;
            }
            $io->table($headers, $lines);

            $debut = (new \DateTime())->setTimestamp($start);
            $fin = (new \DateTime())->setTimestamp($end);

            $periodsOpen = DateTimeUtils::allperiodsBetweenTwo($debut, $fin);

            $io->section("Périodes");
            $io->writeln("Du ".$debut->format('c'). " au " . $fin->format('c'));
            $headers = ["Période", "Conflict", "Total", "Nbr Lot", 'Jours'];
            $rows = [];
            foreach ($periodsOpen as $period) {
                $periodDatas = $timesheetService->getTimesheetDatasPersonPeriod($person, $period);
                $rows[] = [
                    $period,
                    $periodDatas['hasConflict']?'Oui':'Non',
                    number_format($periodDatas['total'], 2),
                    count($periodDatas['workpackages']),
                    $periodDatas['dayNbr'],
                ];
            }
            $io->table($headers, $rows);

        }
        else {
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
                    $out[] = [$personId, $datas['displayname'], $datas['affectation'], count($datas['declarations'])];
                }
                $headers = ['ID', 'Déclarant', 'Affectation', 'Déclaration(s)'];
                $io->table($headers, $out);
            } catch (\Exception $e) {
                $io->error($e->getMessage());
            }
        }
    }
}