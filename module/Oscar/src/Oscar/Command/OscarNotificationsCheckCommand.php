<?php


namespace Oscar\Command;


use Oscar\Service\NotificationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarNotificationsCheckCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'notifications:check';

    protected function configure()
    {
        $this
            ->setDescription("Permet de contrôler les notifications des personnes")
            ->addOption('person', null, InputOption::VALUE_OPTIONAL, 'ID de la personne')
            ->addOption('activity', null, InputOption::VALUE_OPTIONAL, "ID de l'activité");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        /** @var PersonService $personService */
        $personService = $this->getServicemanager()->get(PersonService::class);

        /** @var NotificationService $notificationService */
        $notificationService = $this->getServicemanager()->get(NotificationService::class);

        $personId = $input->getOption('person');
        $activityId = $input->getOption('activity');

        if (!$personId && !$activityId) {
            $io->error(
                "Vous devez préciser la personne (avec --person <IDPERSON>) ou l'activité (avec --activity <IDACTIVITY)"
            );
            exit(1);
        }

        if ($personId) {
            $person = $personService->getPersonById($personId, true);
            $io->title("Contrôle des notifications pour <bold>$person</bold> : ");
            $activities = $notificationService->getNotifiableActivitiesPerson($personId);
            foreach ($activities as $activity) {
                $io->writeln("$activity");
            }
        }
    }
}