<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OscarActivityNotificationUpdateCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = OscarCommandAbstract::COMMAND_ACTIVITY_NOTIFICATION_UPDATE;

    const ARGUMENT_ACTIVITY_ID = 'activityid';

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription("Actualisation des notification pour une activité")
            ->addArgument(self::ARGUMENT_ACTIVITY_ID, InputArgument::REQUIRED, "ID de l'activité");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $activityId = $input->getArgument(self::ARGUMENT_ACTIVITY_ID);

        try {
            $activity = $this->getProjectGrantService()->getActivityById($activityId);

            if (!$this->ask("Actualiser les notifications de l'activité '$activity' ?")) {
                $this->debug("CMD aborded " . $this->getName());
                return 0;
            }
            $this->getProjectGrantService()->getNotificationService()->updateNotificationsActivity($activity);

            return $this->finalSuccess("Notifications mises à jour '$activity'");
        } catch (Exception $e) {
            return $this->finalFatalError($e);
        }
    }
}