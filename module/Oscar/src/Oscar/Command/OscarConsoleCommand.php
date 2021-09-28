<?php


namespace Oscar\Command;


use Oscar\Service\NotificationService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
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

    protected function execute(InputInterface $input, OutputInterface $output)
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

        $io->writeln("Action <bold>$action</bold>");
        $io->writeln("Params \n<bold>". json_encode($params, JSON_PRETTY_PRINT ) ."</bold>");
        $io->writeln("---");

        switch ($action) {
            case "addpersonactivity":
                $person = $personService->getPersonById($params['personid'], true);
                $activity = $projectGrantService->getActivityById($params['activityid']);
                $role = $oscaruserContext->getRoleByRoleId($params['roleid']);

                $personService->personActivityAdd($activity, $person, $role);
                break;

            case "notificationsactivity":

                $activity = $projectGrantService->getActivityById($params['activityid']);
                $notificationService->getLoggerService()->debug("[console:notificationsactivity] $activity");
                $notificationService->updateNotificationsActivity($activity);
                break;

            case "indexactivity":
                $activity = $projectGrantService->getActivityById($params['activityid']);
                $notificationService->getLoggerService()->debug("[console:indexactivity] $activity");
                $projectGrantService->searchUpdate($activity);
                break;

            case "indexperson":
                $person = $personService->getPerson($params['personid']);
                $notificationService->getLoggerService()->debug("[console:indexperson] $person");
                $personService->searchUpdate($person);
                break;

            case "indexorganization":
                $organization = $organizationService->getOrganization($params['organizationid']);
                $notificationService->getLoggerService()->debug("[console:indexorganization] $organization");
                $organizationService->searchUpdate($organization);
                break;

            default :
                break;
        }
    }
}