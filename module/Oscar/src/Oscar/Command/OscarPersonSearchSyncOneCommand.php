<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\ORM\EntityManager;
use Moment\Moment;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
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

class OscarPersonSearchSyncOneCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'person:syncone';

    protected function configure()
    {
        $this
            ->setDescription("Recherche dans l'index de recherche des personnes")
            ->addArgument('connector', InputArgument::REQUIRED, 'Nom du connector')
            ->addArgument('value', InputArgument::REQUIRED, 'Valeur du connector')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);
        $io = new SymfonyStyle($input, $output);

        $connector  = $input->getArgument("connector");
        $value      = $input->getArgument("value");

        $io->title("Recherche pour le connector '$connector' = '$value'");

        /** @var PersonRepository $personRepository */
        $personRepository = $this->getServicemanager()->get(EntityManager::class)->getRepository(Person::class);

        try {
            $io->writeln("$connector => $value");
            $person = $personRepository->getPersonByConnectorID($connector, $value);
            $io->writeln((string)$person);
        } catch ( \Exception $e ){
            $io->error($e->getMessage());
        }
    }
}