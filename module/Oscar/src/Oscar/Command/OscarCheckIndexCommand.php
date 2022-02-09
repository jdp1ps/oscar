<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;

use Monolog\Logger;
use Oscar\Service\OscarConfigurationService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OscarCheckIndexCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = 'check:index';

    protected function configure()
    {
        $this
            ->setDescription("Reconstruction des index (activité, organisation, personne");
    }

    /**
     * @return OscarConfigurationService
     */
    protected function getOscarConfiguration()
    {
        return $this->getServicemanager()->get(OscarConfigurationService::class);
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->getServicemanager()->get('Logger');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        try {
            $this->getIO()->title("Reconstruction de l'index de recherche des activités");
            $this->getProjectGrantService()->searchIndex_rebuild();
            $this->finalSuccess("Index de recherche des activités mis à jour");
        } catch (Exception $e) {
            return $this->finalFatalError($e);
        }

        try {
            $this->getIO()->title("Reconstruction de l'index de recherche des organisations");
            $this->getOrganizationService()->searchIndexRebuild();
            $this->finalSuccess("Index de recherche des organisations mis à jour");
        } catch (Exception $e) {
            return $this->finalFatalError($e);
        }

        try {
            $this->getIO()->title("Reconstruction de l'index de recherche des personnes");
            $this->getPersonService()->searchIndexRebuild();
            $this->finalSuccess("Index de recherche des personnes mis à jour");
        } catch (Exception $e) {
            return $this->finalFatalError($e);
        }

        return 0;
    }
}