<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Service\TimesheetService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OscarCheckDatasCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = 'check:datas';

    protected function configure()
    {
        $this
            ->setDescription("Informations sur les déclarants")
            ->addOption(
                'date-missing',
                null,
                InputOption::VALUE_OPTIONAL,
                'Affiche les activités avec des déclarants dont les dates sont incohérentes',
                null
            );
    }


    protected function formatActivity(array $activities): void
    {
        $headers = ['ID', 'Num', 'Project(Acronym)', 'Label', 'Start', 'End'];
        $rows = [];

        foreach ($activities as $activity) {
            $rows[] = [
                $activity->getId(),
                $activity->getOscarNum(),
                $activity->getAcronym(),
                substr($activity->getLabel(), 0, 25) . '...',
                $activity->getDateStartStr(),
                $activity->getDateEndStr()
            ];
        }

        $this->getIO()->table($headers, $rows);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        // Activités SANS-DATE de début/fin, mais avec les LOTS
        $nodate = $this->getProjectGrantService()
            ->getActivityRepository()
            ->getActivitiesWithWorkPackageDatesMissing();
        $error = 0;
        if(count($nodate)) {
            $error += count($nodate);
            $this->getIO()->title("Activités avec déclaration de temps, mais sans DATE");
            $this->formatActivity($nodate);
        }

        // Activités avec dates étranges
        $paradox = $this->getProjectGrantService()
            ->getActivityRepository()
            ->getActivitiesWithTimeParadox();

        if(count($paradox)) {
            $error += count($paradox);
            $this->getIO()->title("Activités avec des dates de début/fin inversées");
            $this->formatActivity($paradox);
        }


        return self::SUCCESS;
    }

    /**
     * @return TimesheetService
     */
    protected function getTimesheetService()
    {
        return $this->getServicemanager()->get(TimesheetService::class);
    }
}