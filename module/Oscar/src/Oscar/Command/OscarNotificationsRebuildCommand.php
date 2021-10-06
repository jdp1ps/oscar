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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OscarNotificationsRebuildCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = OscarCommandAbstract::COMMAND_NOTIFICATIONS_REBUILD;

    const OPTION_INCLUDE_PAST = 'include-past';
    const OPTION_PURGE = 'purge';

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription("Reconstruction des notifications des activités de recherche")
//            ->addOption(
//                self::OPTION_INCLUDE_PAST,
//                null,
//                InputArgument::OPTIONAL,
//                "Forcer le recalcule des notifications pour les activités"
//            )
            ->addOption(
                self::OPTION_PURGE,
                'p',
                InputOption::VALUE_OPTIONAL,
                "Supprime TOUTES les notifications avant de recalculer",
                false
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        //$includePast = $input->getOption(self::OPTION_INCLUDE_PAST);
        $purgeBefore = $input->getOption(self::OPTION_PURGE);
        var_dump($purgeBefore);

        try {
            // TODO Récupérer les activités en fonction des critères
            $activities = $this->getProjectGrantService()->getActivityRepository()->getActivitiesActive();

            if ($purgeBefore !== false ) {
                if (!$this->ask("Purger les notifications ? ")) {
                    return 0;
                }
                $this->notice("Suppression des notifications...");
                $this->getProjectGrantService()->getNotificationService()->purgeNotificationsAll();
            }

            if (!$this->ask("Recalculer les notifications pour ces " . count($activities) . " activité(s) ?")) {
                return 0;
            }

            foreach ($activities as $activity) {
                $this->info("Recalcule des notifications pour l'activité '$activity'");
                try {
                    $this->getProjectGrantService()->getNotificationService()->updateNotificationsActivity($activity);
                } catch (Exception $e) {
                    return $this->finalFatalError($e);
                }
            }

            return $this->finalSuccess("Les notifications ont été recalculées");
        } catch (Exception $e)
{
return $this->finalFatalError($e);
}
}
}