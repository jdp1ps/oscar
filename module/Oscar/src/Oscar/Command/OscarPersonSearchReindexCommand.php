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

class OscarPersonSearchReindexCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = OscarCommandAbstract::COMMAND_PERSON_SEARCH_REINDEX;

    const ARGUMENT_PERSON_ID = 'personid';

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription("Reconstruction de l'index de recherche pour une personne")
            ->addArgument(self::ARGUMENT_PERSON_ID, InputArgument::REQUIRED, "ID de la personne");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $personId = $input->getArgument(self::ARGUMENT_PERSON_ID);

        try {
            $person = $this->getPersonService()->getPersonById($personId);

            if (!$this->ask("Réindexer la personne '$person' ?")) {
                return 0;
            }

            $this->getPersonService()->searchUpdate($person);

            return $this->finalSuccess("Index de recherche mis à jour pour '$person' mis à jour");
        } catch (Exception $e) {
            return $this->finalFatalError($e);
        }
    }
}