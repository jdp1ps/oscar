<?php


namespace Oscar\Command;


use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarNotificationsMailPersonCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'notifications:mail-person';

    protected function configure()
    {
        $this
            ->setDescription("Force l'envoi du mail à la personne")
            ->addArgument('person', InputArgument::REQUIRED)
            ->addOption('date', 'd', InputOption::VALUE_OPTIONAL, 'date de déclenchement', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);

        $date = $input->getOption('date');
        $dateRef = new \DateTime();
        if( $date ){
            $dateRef = new \DateTime($date);
        }

        /** @var PersonService $personService */
        $personService = $this->getServicemanager()->get(PersonService::class);

        $personLogin = $input->getArgument('person');
        try {
            $person = $personService->getPersonByLdapLogin($personLogin);
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return self::FAILURE;
        }

        try {
            $personService->mailNotificationsPerson($person);
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}