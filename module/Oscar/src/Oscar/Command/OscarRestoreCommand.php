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
            ->addOption('datas', 'd', InputOption::VALUE_REQUIRED, 'Données à exporter')
            ->addOption('directory', 'o', InputOption::VALUE_REQUIRED, 'Dossier où seront créé les fichiers de backup');
    }

    protected function getBackupService(): BackupService
    {
        return $this->getServicemanager()->get(BackupService::class);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);
        $io = new SymfonyStyle($input, $output);

        $datas = $input->getOption('datas');
        $directory = $input->getOption('directory');
        $override = $input->getOption('clean');

        $filesystem = new Filesystem();

        if ($directory) {
            $save_location = Path::normalize($directory);
            $io->writeln("Restoration depuis '$save_location'");
            // On regarde si le dossier existe
            if (!$filesystem->exists($save_location)) {
                $io->error("Le dossier '$save_location' n'existe pas");
                return self::INVALID;
            }
            else {
                $finder = new Finder();
                $io->writeln("Clean directory '$save_location'");
                try {
                    foreach ($finder->files()->in($save_location)->files() as $file) {
                        $io->writeln("Read '$file'");
                    }
                    return self::SUCCESS;
                } catch (\Exception $e) {
                    $io->error("Impossible de vider le réstaurer '$directory' : " . $e->getMessage());
                    return self::FAILURE;
                }
            }
        }

        $expected = BackupService::getAvailables(true);

        if (!$datas) {
            $io->title("Système de BACKUP");
            $io->text("Utilisez une des clefs ci-dessous pour exporter les informations");

            foreach ($expected as $expKey => $expLabel) {
                $io->text(" - <bold>$expKey</bold> : $expLabel");
            }
        }
        else {
            $exported = $this->getBackupService()->export($datas);
            if ($directory) {
                foreach ($exported as $key => $data) {
                    if (in_array($key, BackupService::getAvailables())) {
                        $filename = sprintf('oscar_backup_%s.json', $key);
                        $filepath = Path::normalize($directory . DIRECTORY_SEPARATOR . $filename);
                        $io->writeln(" - Création du fichier '$filepath'");
                        $filesystem->appendToFile($filepath, json_encode($data, JSON_PRETTY_PRINT));
                    }
                }
            }
            else {
                echo json_encode($exported, JSON_PRETTY_PRINT);
            }
        }
        return self::SUCCESS;
    }
}