<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Service\TimesheetService;
use Oscar\Utils\PeriodInfos;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OscarTimesheetHighDelayCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = 'timesheets:high-delay';

    protected function configure()
    {
        $this
            ->setDescription("Relance automatique des retards importants")
            ->addOption("include-nonactive", null, InputOption::VALUE_NONE, "Inclus les activités non-active")
            ->addOption("send", null, InputOption::VALUE_NONE, "Effectue l'envoi réél des mails")
            ->addOption("processdate", null, InputOption::VALUE_OPTIONAL, "Date d'execution", false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $processArg = $input->getOption('processdate');

        $includeNonActive = $input->getOption('include-nonactive') != false;
        $send = $input->getOption('send') !== false;


        if( !$processArg ){
            $period = PeriodInfos::getPeriodInfosObj(date('Y-m'))->prevMonth();
        } else {
            $processDate = new \DateTime($processArg);
            $period = PeriodInfos::getPeriodInfosObj($processDate->format('Y-m'));
        }

        $force = false;

        $this->getIO()->title("Relance dans retards importants pour les déclarants (avant " . $period->getPeriodLabel() . ")");
        $this->getIO()->writeln("OPTION send : " . ($send ? 'on' : 'off'));
        $this->getIO()->writeln("OPTION include-nonactive : " . ($includeNonActive ? 'on' : 'off'));

        // Liste des personnes avec des retards anciens (Personne => Période)
        $datas = $this->getPersonService()->getPersonsHighDelay($period->getPeriodCode(), null, $includeNonActive == 'on');

        $subject = '[OSCAR] Déclaration en retard';
        $body = $this->getOscarConfigurationService()->getHighDelayRelance();


        $validatorsStack = [];

        foreach ($datas as $personDt) {
            if( $personDt['require_alert_declarer'] == true ){
                $email = $personDt['email'];
                $fullname = $personDt['fullname'];
                $this->getIO()->writeln("<green> \u{2714} Envoi au Déclarant $fullname ($email)</green>");

                if( $send ) {
                    try {
                        $message = $this->getPersonService()->getMailingService()->newMessage($subject);
                        $message->setBody($body);
                        $message->addTo($email);
                        $this->getPersonService()->getMailingService()->send($message);
                    } catch (\Exception $e) {
                        $this->getIO()->error($e->getMessage());
                    }
                }

            } else {
               // $this->getIO()->writeln("<info> x pas d'envoi pour le déclarant</info>");
            }

            if( count($personDt['np1']) == 0 ){
                $this->getIO()->error("\u{26A0} Le déclarant '$fullname' n'a pas de validateur administratif pour ces créneaux Hors-Lot");
            }

            // Il y'a des validateurs à soliciter pour ce déclarant
            if( $personDt['require_alert_validator'] == true ){
                if( count($personDt['validators']) ){
                    foreach ($personDt['validators'] as $i=>$infosValidator) {
                        $validatorsStack[$i] = $infosValidator;
                    }
                } else {
                    $this->getIO()->error("\u{26A0} Pas de validateur pour les déclarations de $fullname, vérifier que ces activités ont des validateurs désignés");
                }
            } else {
               // $this->getIO()->writeln("<comment> \u{2716} pas d'envoi pour le validateur (aucune déclaration à valider)</comment>");
            }
        }

        $this->getIO()->title("Envois groupé aux validateurs");

        foreach ($validatorsStack as $i=>$infosValidator) {
            $validatorName = $infosValidator['fullname'];
            $validatorEmail = $infosValidator['email'];

            $this->getIO()->writeln("<green> \u{2714} Envoi validateur : $validatorName ($validatorEmail)</green>");
            if( $send ) {
                $message = $this->getPersonService()->getMailingService()->newMessage($subject);
                $message->setBody($body);
                $message->addTo($validatorEmail);
                $this->getPersonService()->getMailingService()->send($message);
            }
        }

        if( $send == false ){
            $this->getIO()->warning("AUCUN ENVOI EFFECTUE, UTILISEZ --send POUR l'ENVOI EFFECTIF");
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