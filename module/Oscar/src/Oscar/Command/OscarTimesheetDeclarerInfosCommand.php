<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Entity\Person;
use Oscar\Renderer\ConsoleActivityRenderer;
use Oscar\Service\PersonService;
use Oscar\Service\TimesheetService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarTimesheetDeclarerInfosCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:declarer-infos';

    const ARG_DECLARER = "declarer";

    const OPT_PERIOD = "period";
    const OPT_ACTIVITY = "activity";

    protected function configure()
    {
        $this
            ->setDescription("Fourni des informations sur les informations sur les feuilles de temps.")
            ->addOption(self::OPT_ACTIVITY, 'a', InputOption::VALUE_NONE)
            ->addOption(self::OPT_PERIOD, 'p', InputOption::VALUE_OPTIONAL)
            ->addArgument(self::ARG_DECLARER, InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);


        /// OPTIONS and PARAMETERS
        $declarer = $input->getArgument(self::ARG_DECLARER);
        $activity = $input->getOption(self::OPT_ACTIVITY);
        $period = $input->getOption(self::OPT_PERIOD);


        try {
            $declarer = $this->getPersonService()->getPerson($declarer);
            $io->title("Déclarant '$declarer'");
            $io->write("Activités où des déclarations sont attendues : ");

            if ($period) {
                $io->write("pour la période <bold>$period</bold>");
            }
            $io->writeln("");

            if ($activity) {
                $rendererActivity = new ConsoleActivityRenderer($io);
                $activities = $this->getTimesheetService()->getActivitiesDeclarer($declarer->getId(), $period);
                foreach ($activities as $activity) {
                    $rendererActivity->render($activity);
                }
            }
        } catch (\Exception $e) {
            $io->error("Impossible de charger les informations pour le déclarant '$declarer' : " . $e->getMessage());
        }

//        $keycache = "synthese-$activityId";
//        $filecache = "/tmp/$keycache.phpdata";
//        if( file_exists($filecache) ){
//            $datas = unserialize(file_get_contents($filecache));
//        } else {
//            /** @var Activity $activity */
//            $activity = $this->getTimesheetService()->getEntityManager()->getRepository(Activity::class)->find($activityId);
//            $periodsStrs = DateTimeUtils::allperiodsBetweenTwo($activity->getDateStart(), $activity->getDateEnd());
//
//            $datas = [
//                'periods' => []
//            ];
//            foreach ($periodsStrs as $periodStr) {
//                $datas['periods'][$periodStr] = $this->getTimesheetService()->getSynthesisActivityPeriod($activityId, $periodStr);
//            }
//            file_put_contents($filecache, serialize($datas));
//        }
//
//        var_dump($datas);

        return;
    }

    /**
     * @return TimesheetService
     */
    protected function getTimesheetService()
    {
        return $this->getServicemanager()->get(TimesheetService::class);
    }

    /**
     * @return PersonService
     */
    protected function getPersonService()
    {
        return $this->getServicemanager()->get(PersonService::class);
    }

    public function declarer(InputInterface $input, OutputInterface $output, $declarerId)
    {
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

    public function declarersList(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Lite des déclarants");
        try {
            $declarants = $this->getTimesheetService()->getDeclarers();
            $out = [];
            /** @var Person $declarer */
            foreach ($declarants['persons'] as $personId => $datas) {
                $out[] = [$personId, $datas['displayname'], $datas['affectation'], count($datas['declarations'])];
            }
            $headers = ['ID', 'Déclarant', 'Affectation', 'Déclaration(s)'];
            $io->table($headers, $out);

            $io->comment("Entrez la commande '" . self::getName() . " <ID> [PERIOD]' pour afficher les détails");
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}