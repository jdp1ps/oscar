<?php


namespace Oscar\Command;


use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarNotificationsMailsPersonsCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'notifications:mails-persons';

    protected function configure()
    {
        $this
            ->setDescription("Déclenche la procédure d'envoi des notifications par mail")
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
            $dateRef = new \DateTime(date('Y-m-d ') . $date);
        }

        /** @var PersonService $personService */
        $personService = $this->getServicemanager()->get(PersonService::class);

        $personService->mailPersonsWithUnreadNotification($dateRef->format('Y-m-d H:i:s'), $io);
    }
}