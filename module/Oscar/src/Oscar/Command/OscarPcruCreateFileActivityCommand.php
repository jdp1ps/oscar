<?php


namespace Oscar\Command;


use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PCRUService;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarPcruCreateFileActivityCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'pcru:generate-file';

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

        // Récupération des données
        $numeroOscar = $input->getOption('oscarid');
        if( !$numeroOscar ){
            $io->error("Vous devez renseigner un IDOSCAR");

            $activities = $pcruService->getActivitiesAvailable();

            $io->title("Informations PCRU disponibles");
            if( count($activities) == 0 ){
                $io->warning("Aucune activité n'a de données PCRU prête");
            }
            $out = [];

        }

        die($numeroOscar);


        if( !$configuration->getPcruEnabled() ){
            $io->error("Le module PCRU n'est pas actif");
        } else {
            var_dump($pcruService->upload());
        }
    }
}