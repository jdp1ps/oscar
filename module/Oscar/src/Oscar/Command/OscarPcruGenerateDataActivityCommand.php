<?php


namespace Oscar\Command;


use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPcruInfos;
use Oscar\Factory\ActivityPcruInfoFromActivityFactory;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PCRUService;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarPcruGenerateDataActivityCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'pcru:generate-data';

    protected function configure()
    {
        $this
            ->setDescription("Génération des données PCRU pour l'envoi.")
            ->addOption('path', 'd', InputOption::VALUE_OPTIONAL, 'Dossier où créer les fichiers à transmettre (Utilise la dossier en configuration par défaut)')
            ->addOption('purge', 'p', InputOption::VALUE_OPTIONAL, "Une fois le transfert terminé, les fichiers sont supprimés localement", false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);
//        /** @var OscarConfigurationService $configuration */
//        $configuration = $this->getServicemanager()->get(OscarConfigurationService::class);
        /** @var ProjectGrantService $activityService */
        $activityService = $this->getServicemanager()->get(ProjectGrantService::class);

        /** @var PCRUService $pcruService */
        $pcruService = $activityService->getPCRUService();

        /** @var SymfonyStyle $io */
        $io = new SymfonyStyle($input, $output);

        $path = $input->getOption('path');
        if( !$path )
            $path = $pcruService->getOscarConfigurationService()->getPcruDirectoryForUpload();


        try {
            $io->title("Build files fr PCRU into $path");
            $pcruCsv = $pcruService->generatePcruFiles(null, $io);
            echo $pcruCsv->makeZip();
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }

        return 0;
    }
}