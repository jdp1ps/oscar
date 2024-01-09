<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Moment\Moment;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\Service\ConnectorService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarPersonsSearchRebuildCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'persons:search-rebuild';

    protected function configure()
    {
        $this
            ->setDescription("Execute la reconstruction de l'index de recherche des personnes")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Reconstruction de l'index de recherche des personnes");

        /** @var PersonService $personService */
        $personService = $this->getServicemanager()->get(PersonService::class);

        try {
            $persons = $personService->getPersons();
            $personService->getSearchEngineStrategy()->rebuildIndex($persons);
            $io->success(sprintf('Index de recherche mis à jour avec %s personnes indexées', count($persons)));
        } catch ( \Exception $e ){
            $io->error($e->getMessage());
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}