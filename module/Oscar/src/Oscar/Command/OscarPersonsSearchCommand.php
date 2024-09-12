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

class OscarPersonsSearchCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'persons:search';

    protected function configure()
    {
        $this
            ->setDescription("Recherche dans l'index de recherche des personnes")
            ->addArgument('search', InputArgument::REQUIRED, 'Expression de recherche')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);
        $search = $input->getArgument("search");
        $io->title("Recherche '$search' dans les personnes : ");

        /** @var PersonService $personService */
        $personService = $this->getServicemanager()->get(PersonService::class);

        try {
            $ids = $personService->getSearchEngineStrategy()->search($search);
            if( count($ids) ){
                $persons = $personService->getPersonsByIds($ids);
                $headers = ["ID", "SYNC", "Nom complet", "Prénom", "Nom", "Affectation", "Email"];
                $datas = [];
                foreach ($persons as $person) {
                    $datas[] = [
                        '<bold>[' . $person->getId() .']</bold>',
                        $person->getConnectorsDatasStr(),
                        $person->getDisplayName(),
                        $person->getFirstname(),
                        $person->getLastname(),
                        $person->getLdapAffectation(),
                        $person->getEmail()
                    ];
//                    $io->writeln( sprintf('- <bold>[%s]</bold> %s (%s)', $person->getId(), $person->getDisplayName(), $person->getEmail()));
                }
                $io->table($headers, $datas);
            } else {
                $io->warning("Aucun résultats");
            }
        } catch ( \Exception $e ){
            $io->error($e->getMessage());
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}