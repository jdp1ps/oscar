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

class OscarOrganizationSearchReindexCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = OscarCommandAbstract::COMMAND_ORGANIZATION_SEARCH_REINDEX;

    const ARGUMENT_ORGANIZATION_ID = 'organizationid';

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription("Reconstruction de l'index de recherche pour une organisation")
            ->addArgument(self::ARGUMENT_ORGANIZATION_ID, InputArgument::REQUIRED, "ID de l'organisation");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $organizationid = $input->getArgument(self::ARGUMENT_ORGANIZATION_ID);

        try {
            $organization = $this->getOrganizationService()->getOrganization($organizationid);

            if (!$this->ask("Réindexer l'organisation '$organization' ?")) {
                return 0;
            }

            $this->getOrganizationService()->searchUpdate($organization);

            return $this->finalSuccess("Index de recherche mis à jour pour '$organization' mis à jour");
        } catch (Exception $e) {
            return $this->finalFatalError($e);
        }
    }
}