<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OscarActivitySearchRebuildCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = OscarCommandAbstract::COMMAND_ACTIVITY_SEARCH_REINDEX_ALL;

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription("Reconstruction de l'index de recherche pour TOUTES les activités");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        try {
            $this->getIO()->title("Reconstruction de l'index de recherche des activités");
            $this->getProjectGrantService()->searchIndex_rebuild();
            return $this->finalSuccess("Index de recherche mis à jour");
        } catch (Exception $e) {
            return $this->finalFatalError($e);
        }
    }
}