<?php


namespace Oscar\Command;


use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarNotificationsMailsPersonsCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'notifications:mails-persons';

    protected function configure()
    {
        $this
            ->setDescription("Déclenche la procédure d'envoi des notifications par mail")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $io = new SymfonyStyle($input, $output);

        /** @var PersonService $personService */
        $personService = $this->getServicemanager()->get(PersonService::class);

        $personService->mailPersonsWithUnreadNotification(null, $io);
    }
}