<?php

namespace Oscar\Command;


use Oscar\Service\ActivityTypeService;
use Oscar\Service\BackupService;
use Oscar\Service\OscarUserContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarBackupCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'backup';

    protected function configure()
    {
        $this
            ->setDescription("Génération de données JSON")
            ->addOption('datas', 'd', InputOption::VALUE_REQUIRED, 'Données à exporter');
    }

    protected function getBackupService() :BackupService
    {
        return $this->getServicemanager()->get(BackupService::class);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $datas = $input->getOption('datas');
        $expected = [
            BackupService::ACTIVITY_TYPES => "Types d'activité",
            BackupService::PERSONS => "Personnes",
        ];

        if(!$datas){
            $io = new SymfonyStyle($input, $output);

            $io->title("Système de BACKUP");
            $io->text("Utilisez une des clefs ci-dessous pour exporter les informations");

            foreach ($expected as $expKey=>$expLabel) {
                $io->text(" - <bold>$expKey</bold> : $expLabel");
            }
        } else {

            echo json_encode($this->getBackupService()->export($datas), JSON_PRETTY_PRINT);

        }
        return self::SUCCESS;
    }
}