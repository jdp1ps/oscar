<?php

namespace Oscar\Command;


use Oscar\OscarVersion;
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

class OscarInfosCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'infos';

    protected function configure()
    {
        $this
            ->setDescription("Informations sur la version installée (JSON)")
            ->addOption('stdout', 's', InputOption::VALUE_NONE, "Affiche le résultat dans la sortie standard")
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, "Fichier de sortie", "oscar-info.json")
            ->addOption('logs', 'l', InputOption::VALUE_OPTIONAL, "Nombre de logs", 30);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);
        $io = new SymfonyStyle($input, $output);
        $commitHash = trim(exec('git log -1 --pretty="%h" -n1 HEAD'));
        $branch = trim(exec('git branch | grep \* | cut -d \' \' -f2'));

        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
        $commitDate->setTimezone(new \DateTimeZone('UTC'));

        $split = "¤¤~" . uniqid() . "~¤¤";
        exec(
            "git log --date=iso-local --pretty=format:'$split%H$split%an$split%ad$split%s'",
            $logs
        );

        $tag = trim(exec("git tag --points-at HEAD"));

        $infos = [
            "app"        => "Oscar",
            "name"       => OscarVersion::NAME,
            "version"    => OscarVersion::MAJOR . "." . OscarVersion::MINOR . "." . OscarVersion::PATCH,
            "branch"     => $branch,
            "tag"        => $tag,
            "dateUpdate" => $commitDate->format("Y-m-d H:i:s"),
            "commitHash" => $commitHash,
            "logs"       => []
        ];

        $cap = $limit = intval($input->getOption('logs'));

        foreach ($logs as $line) {
            if ($limit-- < 0) {
                break;
            }
            $dt = explode($split, $line);
            $log = [
                "commit_short" => substr($dt[1], 0, 8),
                "commit"       => $dt[1],
                "author"       => $dt[2],
                "date"         => $dt[3],
                "message"      => $dt[4],
            ];
            $infos['logs'][] = $log;
        }


        if ($input->getOption('stdout')) {
            $io->title(sprintf("%s (%s v%s)", $infos['app'], $infos['name'], $infos['version']));
            $io->writeln("branch: <bold>" . $infos['branch'] . "</bold>");
            $io->writeln("Dernier commi: <bold>" . $infos['commitHash'] . "</bold>");
            $io->writeln("Date: <bold>" . $infos['dateUpdate'] . "</bold>");

            if ($cap > 0) {
                $io->section("Logs");
                $headers = ["Commit", "Author", "Date", "Message"];
                $rows = [];
                foreach ($infos['logs'] as $log) {
                    $rows[] = [
                        $log['commit_short'],
                        $log['author'],
                        $log['date'],
                        $log['message']
                    ];
                }
                $io->table($headers, $rows);
            }
        }

        $json = json_encode($infos, JSON_PRETTY_PRINT);
        $file = $input->getOption('file');

        if (file_exists($file) && !is_writable($file)) {
            $io->error("Impossible d'écrire '$file'");
            return self::FAILURE;
        }
        if (!@file_put_contents($file, $json)) {
            $io->error("Un problème est survenu lors de l'écriture du fichier '$file'");
            return self::FAILURE;
        }
        $io->success("Fichier d'information '$file' mis à jour");

        return self::SUCCESS;
    }
}