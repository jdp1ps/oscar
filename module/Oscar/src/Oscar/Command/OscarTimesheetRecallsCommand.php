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
use Oscar\Utils\PeriodInfos;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\Validator\Date;

class OscarTimesheetRecallsCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = 'timesheets:recalls';

    protected function configure()
    {
        $this
            ->setDescription("Relance automatique des feuilles de temps")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $period = PeriodInfos::getPeriodInfosObj(date('Y-m'))->prevMonth();
        $force = false;

        // Date de déclenchement de la procédure
        $processDate = new \DateTime();

        $this->getIO()->title("Relance pour " . $period->getPeriodLabel());

        $declarers = $this->getPersonService()->getPersonsByIds(
                $this->getPersonService()->getDeclarersIdsPeriod($period->getPeriodCode())
        );

        foreach ($declarers as $declarer) {
            $result = $this->getTimesheetService()->recallProcess($declarer->getId(), $period->getPeriodCode(), $processDate, $force);
            if( $result['mailSend'] ){
                $snd = "<green>Mail envoyé</green>";
            } else {
                if( $result['blocked'] ){
                    $snd = "<red>Bloqué</red>";
                } else {
                    $snd = "<none>Pas d'envoi</none>";
                }
            }
            $snd = $snd ." ". $result['recall_info'];
            $this->getIO()->writeln(" - <bold>$declarer</bold> : $snd");
        }

        return 0;
    }


    /**
     * @return TimesheetService
     */
    protected function getTimesheetService(){
        return $this->getServicemanager()->get(TimesheetService::class);
    }
}