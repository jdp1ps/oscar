<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Entity\Person;
use Oscar\Entity\RecallDeclaration;
use Oscar\Service\TimesheetService;
use Oscar\Utils\PeriodInfos;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OscarTimesheetRecallsValidatorsCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = 'timesheets:recalls-validators';

    protected function configure()
    {
        $this
            ->setDescription("Relance automatique pour les validateurs")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);


        $this->getIO()->title("Relance pour les validateurs");

        // Liste des périodes à valider
        $periods = $this->getTimesheetService()->getValidationPeriodRepository()->getValidationPeriodsUnvalidated();

        $headers = ['ID', "Déclarant", "Période", "Statut", "Validateurs"];
        $rows = [];

        foreach ($periods as $p) {
            $validators = [];
            /** @var Person $validator */
            foreach ($p->getCurrentValidators() as $validator) {
                $validators[$validator->getEmail()] = (string)$validator;
            }
            $rows[] = [
                $p->getId(),
                $p->getDeclarer(),
                $p->getPeriod(),
                $p->getStatus(),
                implode(", ", $validators),
            ];
        }

        $this->getIO()->table($headers, $rows);

        $this->getTimesheetService()->notificationValidators($periods);

        return self::SUCCESS;
    }


    /**
     * @return TimesheetService
     */
    protected function getTimesheetService(){
        return $this->getServicemanager()->get(TimesheetService::class);
    }
}