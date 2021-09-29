<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Moment\Moment;
use Oscar\Connector\ConnectorRepport;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\Service\ConnectorService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarActivitySearchReindexCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = 'activity:search:reindex';

    const ARGUMENT_ACTIVITY_ID = 'activityid';

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription("Reconstruction de l'index de recherche pour une activité")
            ->addArgument(self::ARGUMENT_ACTIVITY_ID, InputArgument::REQUIRED, "ID de l'activité")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) :int
    {
        parent::execute($input, $output);

        $activityId = $input->getArgument(self::ARGUMENT_ACTIVITY_ID);

        try {
            $this->debug("Récupération de l'activité $activityId");
            $activity = $this->getProjectGrantService()->getActivityById($activityId);

            if( !$this->ask("Réindexer l'activité '$activity' ?") ){
                return 0;
            }

            $this->getProjectGrantService()->searchUpdate($activity);

            return $this->finalSuccess("Index de recherche mis à jour pour '$activity' mis à jour");


        } catch (\Exception $e ){
            return $this->finalFatalError($e);
        }

        return 1;
    }
}