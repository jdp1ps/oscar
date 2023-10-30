<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Entity\TimeSheet;
use Oscar\Service\TimesheetService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OscarTimesheetImportCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = 'timesheets:import';

    const ARG_FILE = 'file';

    protected function configure()
    {
        $this
            ->setDescription("Importation de feuille de temps.")
            ->addArgument(self::ARG_FILE, InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) :int
    {
        parent::execute($input, $output);

        /// OPTIONS and PARAMETERS
        $filepath = $input->getArgument(self::ARG_FILE);

        $timesheets = json_decode(file_get_contents($filepath), true);

        try {
            // Récupération des créneaux importés
            $alreadyImportedUid = [];
            foreach ($timesheets as $timesheetData) {
                $alreadyImportedUid[] = $timesheetData['uid'];
            }
            $importedTimesheets = $this->getTimesheetService()->getImportedTimesheetsByUid($alreadyImportedUid);

            // Configuration des hors-lots
            $othersConfig = $this->getTimesheetService()->getOthersWP();

            foreach ($timesheets as $timesheetData) {
                $tsUID = $timesheetData['uid'];
                if( array_key_exists($tsUID, $importedTimesheets) ){
                    $this->getIO()->writeln("<info>Entrée '$tsUID' déjà importée</info>");
                    continue;
                }

                // On distingue les créneaux de projet/hors-lot
                $tsActivityUid = $timesheetData['activity_uid'];
                $tsOtherCode = $timesheetData['other_key'];
                $tsWpCode = $timesheetData['workpackage_code'];

                if( $tsActivityUid == null && $tsWpCode == null && $tsOtherCode == null ){
                    $this->getIO()->error("L'entrée $tsUID est incomplète (référence Activité/Hors-lots non-définie)");
                    continue;
                }

                // --- Récupération du déclarant
                $tsDeclarerEmail = $timesheetData['declarer_email'];
                $declarer = $this->getPersonService()->getPersonByEmail($tsDeclarerEmail);

                $activity = null;
                $workpackage = null;
                $label = null;

                // --- Récupération de l'activité
                if( $tsActivityUid ){
                    $activity = $this->getProjectGrantService()->getActivityByImportedUid($tsActivityUid);

                    if( !$activity->hasDeclarant($declarer) ){
                        $this->getIO()->warning("Le créneau '$tsUID' ne peut être importer pour '$declarer', 
                        il n'est pas identifié comme déclarant sur l'activité");
                        continue;
                    }

                    $workpackage = $activity->getWorkpackageByCode($tsWpCode);
                    if (!$workpackage ){
                        $this->getIO()->warning("Impossible de charger le lot '$tsWpCode' dans '$activity'");
                        continue;
                    }
                    $label = (string)$workpackage;
                } else {
                    if( !array_key_exists($tsOtherCode, $othersConfig) ){
                        $this->getIO()->warning("Impossible le créneau '$tsOtherCode', 
                            ce type hors-lot est absent de la configuration");
                        continue;
                    }
                    $label = $tsOtherCode;
                }

                // --- Calibration de la durée du créneau
                $day = $timesheetData['date'];
                $duration = $timesheetData['duration'];
                $comment = $timesheetData['comment'];
                $from = new \DateTime("$day 08:00:00");
                $to = new \DateTime("$day 08:00:00");
                $hours = intval($duration);
                $minutes = intval(($duration-$hours)*60);
                $interval = \DateInterval::createFromDateString("$hours hours $minutes minutes");
                $to->add($interval);

                // --- Enregistrement
                $timesheet = new TimeSheet();
                $timesheet->setPerson($declarer)
                    ->setWorkpackage($workpackage)
                    ->setLabel($label)
                    ->setActivity($activity)
                    ->setComment($comment)
                    ->setDateFrom($from)
                    ->setDateTo($to)
                    ->setDateSync((new \DateTime()))
                    ->setIcsUid($tsUID);

                $this->getIO()->writeln("<success>Importation de $timesheet</success>");
                $this->getTimesheetService()->getEntityManager()->persist($timesheet);
                $this->getTimesheetService()->getEntityManager()->flush($timesheet);

            }
        } catch (\Exception $e) {
            $this->getIO()->error("Impossible d'importer les créneaux : " . $e->getMessage());
            return 1;
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