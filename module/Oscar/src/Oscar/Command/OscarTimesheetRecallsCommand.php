<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Entity\RecallDeclaration;
use Oscar\Service\TimesheetService;
use Oscar\Utils\PeriodInfos;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OscarTimesheetRecallsCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = 'timesheets:recalls';

    protected function configure()
    {
        $this
            ->setDescription("Relance automatique des feuilles de temps")
            ->addOption("purge", null, InputOption::VALUE_OPTIONAL, "Suppression des données (dev)", false)
            ->addOption("preview", null, InputOption::VALUE_OPTIONAL, "Aperçu (affiche les personnes concernées)", false)
            ->addOption("processdate", null, InputOption::VALUE_OPTIONAL, "Date d'execution", false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $processArg = $input->getOption('processdate');

        $purge = $input->getOption('purge');
        $preview = $input->getOption('preview') !== false;

        if( $purge === null ){
            $recalls = $this->getOrganizationService()->getEntityManager()->getRepository(RecallDeclaration::class)->findAll();
            if($this->ask("Reset complet des procédures de rappel ? (y)")){
                foreach ($recalls as $r) {
                    $this->getOrganizationService()->getEntityManager()->remove($r);
                }
                $this->getOrganizationService()->getEntityManager()->flush();
                die();
            } else {
                $this->getIO()->writeln("Annulé");
            }
        }

        if( !$processArg ){
            $processDate = new \DateTime();
            $period = PeriodInfos::getPeriodInfosObj(date('Y-m'))->prevMonth();
        } else {
            $processDate = new \DateTime($processArg);
            $period = PeriodInfos::getPeriodInfosObj($processDate->format('Y-m'));
        }

        $force = false;

        $this->getIO()->title("Relance pour les déclarants " . $period->getPeriodLabel());

        $declarers = $this->getPersonService()->getPersonsByIds(
                $this->getPersonService()->getDeclarersIdsPeriod($period->getPeriodCode())
        );

        foreach ($declarers as $declarer) {
//            if( $declarer->getId() != 5063 ) continue;

            try {
                $result = $this->getTimesheetService()->recallProcess(
                    $declarer->getId(), $period->getPeriodCode(), $processDate, $force, $preview);
            } catch (\Exception $e) {
                $this->getIO()->error($e->getMessage());
                continue;
            }
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

        $this->getIO()->title("Relance pour les validateurs " . $period->getPeriodLabel());

        $validators = $this->getPersonService()->getPersonsByIds(
            $this->getPersonService()->getValidatorsIdsPeriod($period->getPeriodCode())
        );

        foreach ($validators as $validator) {
            $result = $this->getTimesheetService()->recallValidatorProcess(
                $validator->getId(),
                $period->getYear(),
                $period->getMonth(),
                $processDate,
                $force,
                $preview
            );

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
            $this->getIO()->writeln(" - <bold>$validator</bold> : $snd");
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