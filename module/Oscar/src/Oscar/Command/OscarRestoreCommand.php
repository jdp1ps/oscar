<?php

namespace Oscar\Command;


use Oscar\Service\ActivityTypeService;
use Oscar\Service\BackupService;
use Oscar\Service\OscarUserContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

class OscarRestoreCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'restore';

    protected function configure()
    {
        $this
            ->setDescription("Restauration des données")
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Fichier à restaurer')
            ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Dossier contenant les fichiers à restaurer');
    }

    protected function getBackupService(): BackupService
    {
        return $this->getServicemanager()->get(BackupService::class);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);
        $io = new SymfonyStyle($input, $output);

        $file = $input->getOption('file');
        $keysAvailable = BackupService::getAvailables();
        $json = json_decode(file_get_contents($file), true);
        foreach ($json as $key => $value) {
            if( in_array($key, $keysAvailable) ){
                $io->title("Traitement de '$key'");
                $this->getBackupService()->restore($key, $value);
            }
        }

        return self::SUCCESS;
    }
}