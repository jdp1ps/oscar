<?php


namespace Oscar\Command;


use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PCRUService;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarPcruSendCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'pcru:send';

    protected function configure()
    {
        $this
            ->setDescription("Envoi des données PCRU.")
            ->addOption('oscarid', 'u', InputOption::VALUE_OPTIONAL, 'N° Oscar', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarConfigurationService $configuration */
        $configuration = $this->getServicemanager()->get(OscarConfigurationService::class);

        /** @var PCRUService $pcruService */
        $pcruService = $this->getServicemanager()->get(PCRUService::class);

        /** @var SymfonyStyle $io */
        $io = new SymfonyStyle($input, $output);

        if( !$configuration->getPcruEnabled() ){
            $io->error("Le module PCRU n'est pas actif");
        } else {
            var_dump($pcruService->upload());
        }
    }
}