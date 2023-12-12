<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;

use Exception;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\NotificationRepository;
use Oscar\Service\MilestoneService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OscarNotificationsRunCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = OscarCommandAbstract::COMMAND_NOTIFICATIONS_RUN;

    const OPTION_DATEREF = 'date';
    const OPTION_PERSON = 'show-persons';
    const OPTION_USER = 'users';

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription("Calcule des notifications")
            ->addOption(
                self::OPTION_DATEREF,
                'd',
                InputOption::VALUE_OPTIONAL,
                "Date de référence pour le calcule des notifications",
                false
            )
            ->addOption(
                self::OPTION_PERSON,
                'p',
                InputOption::VALUE_NONE,
                "Afficher les personnes impliquées"
            )
            ->addOption(
                self::OPTION_USER,
                'u',
                InputOption::VALUE_REQUIRED,
                "Affiche uniquement pour la personne (identifiant)"
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $io = $this->getIO();

        $dateRef = $input->getOption(self::OPTION_DATEREF);
        $displayPersons = $input->getOption(self::OPTION_PERSON);
        $user = $input->getOption(self::OPTION_USER);

        if ($dateRef === false) {
            $date = new \DateTime();
        } else {
            try {
                $date = new \DateTime($dateRef);
            } catch (Exception $e) {
                $io->error("Mauvaise date : " . $e->getMessage());
                return self::INVALID;
            }
        }

        $infoDate = $date->format("d M Y");

        try {
            /** @var MilestoneService $milestoneService */
            $milestoneService = $this->getProjectGrantService()->getMilestoneService();

            $headers = [
                "ID",
                "Projet",
                "Activité",
                "Type",
                "Date",
                "Nature du rappel"
            ];


            $person = null;
            if( $user ){
                try {
                    $person = $this->getPersonService()->getPersonByLdapLogin($user);
                } catch (Exception $e) {
                    $io->error("Impossible de trouver la personne associée à l'identifiant '$user'");
                    return self::FAILURE;
                }
                $io->title("Calcule des notifications pour $person($user) le $infoDate (v2)");
            } else {
                $io->title("Calcule des notifications le $infoDate (v2)");
            }

            if ($displayPersons) {
                $milestones = $milestoneService->getMilestonesRecallableWithPersons($date);
                $headers[] = "Personnes";
            } else {
                $milestones = $milestoneService->getMilestonesRecallableAtDate($date, $person);
            }

            $io->table($headers, $milestones);
        } catch (Exception $e) {
            return $this->finalFatalError($e);
        }
        return self::SUCCESS;
    }
}