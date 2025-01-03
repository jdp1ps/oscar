<?php


namespace Oscar\Command;


use Oscar\Service\NotificationService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarConsoleCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'console';

    protected function configure()
    {
        $this
            ->setDescription("Permet de contrôler les notifications des personnes")
            ->addArgument('action', null, InputOption::VALUE_OPTIONAL, 'action')
            ->addArgument('params', null, InputOption::VALUE_OPTIONAL, "paramêtres");
    }

    protected function execute(InputInterface $input, OutputInterface $output) :int
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        /** @var OrganizationService $organizationService */
        $organizationService = $this->getServicemanager()->get(OrganizationService::class);

        /** @var PersonService $personService */
        $personService = $this->getServicemanager()->get(PersonService::class);

        /** @var ProjectGrantService $personService */
        $projectGrantService = $this->getServicemanager()->get(ProjectGrantService::class);

        /** @var NotificationService $notificationService */
        $notificationService = $this->getServicemanager()->get(NotificationService::class);

        $action = $input->getArgument('action');

        $params = json_decode($input->getArgument('params'), true);

        $this->getServicemanager()->get('Logger')->debug("command : $action " . $input->getArgument('params'));

        switch ($action) {
            case "notificationsactivity":

                $command = $this->getApplication()->find(OscarCommandAbstract::COMMAND_ACTIVITY_NOTIFICATION_UPDATE);

                $arguments = [
                    OscarActivityNotificationUpdateCommand::ARGUMENT_ACTIVITY_ID => $params['activityid'],
                    '--force' => null
                ];

                $input = new ArrayInput($arguments);
                return $command->run($input, $output);

            case "indexactivity":

                $command = $this->getApplication()->find(OscarCommandAbstract::COMMAND_ACTIVITY_SEARCH_REINDEX);

                $arguments = [
                    OscarActivitySearchReindexCommand::ARGUMENT_ACTIVITY_ID => $params['activityid'],
                    '--force' => null
                ];

                $input = new ArrayInput($arguments);
                return $command->run($input, $output);

            case "indexperson":
                $command = $this->getApplication()->find(OscarCommandAbstract::COMMAND_PERSON_SEARCH_REINDEX);

                $arguments = [
                    OscarPersonSearchReindexCommand::ARGUMENT_PERSON_ID => $params['personid'],
                    '--force' => null
                ];

                $input = new ArrayInput($arguments);
                return $command->run($input, $output);

            case "indexorganization":
                $command = $this->getApplication()->find(OscarCommandAbstract::COMMAND_ORGANIZATION_SEARCH_REINDEX);

                $arguments = [
                    OscarOrganizationSearchReindexCommand::ARGUMENT_ORGANIZATION_ID => $params['organizationid'],
                    '--force' => null
                ];

                $input = new ArrayInput($arguments);
                return $command->run($input, $output);

            default :
                $this->getServicemanager()->get('Logger')->error("BAD COMMAND $action");
                return self::FAILURE;
        }
    }
}