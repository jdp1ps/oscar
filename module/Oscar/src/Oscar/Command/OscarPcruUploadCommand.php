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

class OscarPcruUploadCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'pcru:upload-data';

    protected function configure()
    {
        $this
            ->setDescription("Envoi desdonnées PCRU.")
            ->addArgument('path', InputArgument::REQUIRED, 'Emplacement où créer les fichiers à transmettre')
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

        $path = realpath($input->getArgument('path'));

        if (!$path) {
            $io->error("Cet emplacement n'est pas accessible");
            exit(1);
        }

        try {
            $pcruService->upload($path, $io);

//            $contactCsv = $path.DIRECTORY_SEPARATOR.'contrat.csv';
//            $io->title($contactCsv);
//            if (($handle = fopen($contactCsv, "r")) !== FALSE) {
//                $row = 0;
//                while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
//                    if( $row == 0 ) {
//                        $row++; continue;
//                    }
//                    // N° Oscar
//                    $numOscar = $data[3];
//                    $info = $pcruService->getInfosByNumOscar($numOscar);
//
//
//                }
//                fclose($handle);
//            }

        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }

        return 0;
    }
}