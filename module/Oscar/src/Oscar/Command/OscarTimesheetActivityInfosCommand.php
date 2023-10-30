<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Entity\Person;
use Oscar\Service\PersonService;
use Oscar\Service\TimesheetService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarTimesheetActivityInfosCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'timesheets:activity-infos';

    const ARG_ACTIVITY      = "activity";

    protected function configure()
    {
        $this
            ->setDescription("Fourni des informations sur les informations sur les feuilles de temps.")
            ->addArgument(self::ARG_ACTIVITY)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);


        /// OPTIONS and PARAMETERS
        $activityId = $input->getArgument(self::ARG_ACTIVITY);

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