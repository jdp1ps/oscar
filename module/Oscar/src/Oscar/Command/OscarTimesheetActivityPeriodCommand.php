<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Moment\Moment;
use Oscar\Entity\Activity;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Formatter\TimesheetActivityPeriodFormatter;
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

class OscarTimesheetActivityPeriodCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:activity-period-infos';

    const ARG_ACTIVITY      = "activity";
    const ARG_PERIOD        = "period";
    const ARG_FORMAT        = "format";

    protected function configure()
    {
        $this
            ->setDescription("Fourni des informations sur les informations de temps pour un déclarant à la période donnée")
            ->addArgument(self::ARG_ACTIVITY, InputArgument::REQUIRED, "login du déclarant")
            ->addArgument(self::ARG_PERIOD, InputArgument::REQUIRED, "periode sous la forme YYYY-MM")
            ->addArgument(self::ARG_FORMAT, InputArgument::REQUIRED, "format (pdf, xml, json, csv)")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /// OPTIONS and PARAMETERS
        $activityId = $input->getArgument(self::ARG_ACTIVITY);
        $periodStr = $input->getArgument(self::ARG_PERIOD);
        $format = $input->getArgument(self::ARG_FORMAT);

        $keycache = "synthese-$activityId-$periodStr";
        $filecache = "/tmp/$keycache.phpdata";
        if( file_exists($filecache) ){
            $datas = unserialize(file_get_contents($filecache));
        } else {
            $periodsStrs = [];
            if($periodStr == 'all') {
                /** @var Activity $activity */
                $activity = $this->getTimesheetService()->getEntityManager()->getRepository(Activity::class)->find($activityId);
                $periodsStrs = DateTimeUtils::allperiodsBetweenTwo($activity->getDateStart(), $activity->getDateEnd());
            }
            $datas = [
                'periods' => []
            ];
            foreach ($periodsStrs as $periodStr) {
                $datas['periods'][$periodStr] = $this->getTimesheetService()->getSynthesisActivityPeriod($activityId, $periodStr);
            }
            file_put_contents($filecache, serialize($datas));
        }

        ksort($datas['periods']);

        if( $format == "xls" ) {
            $formatter = new TimesheetActivityPeriodFormatter();
            $formatter->output($datas);
        }

        return;
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

    public function declarerPeriod( InputInterface $input, OutputInterface $output, $declarerId, $period ){
        // TODO Faire un rendu text des déclarations mensuelles des déclarants
        $datas = $this->getTimesheetService()->getTimesheetDatasPersonPeriod($this->getPersonService()->getPerson($declarerId), $period);
        echo "Non-disponible";
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