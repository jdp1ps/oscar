<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Service\ProjectGrantService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OscarInitDataPoleCompetiviteCommand extends OscarJsonInitDataLoaderCommandAbstract
{
    protected static $defaultName = 'initdata:pcru-pole-competivite';

    protected function configure()
    {
        $this
            ->setDescription("Charge les pôles de compétivités par défaut (officiel CNRS)")
            ->setHelp("")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $output->writeln("<title>### CHARGEMENT des PÔLES de COMPETIVITé ###</title>");
        $jsonSource = __DIR__ . "/../../../../../install/pcru-pole-competitivite.json";
        $output->writeln("Chargement depuis <bold>".realpath($jsonSource)."</bold>");
        $output->writeln("...");

        $datas = json_decode(file_get_contents($jsonSource));

        /** @var ProjectGrantService $projectGrantService */
        $projectGrantService = $this->getServicemanager()->get(ProjectGrantService::class);

        foreach ($datas as $label) {
            $output->write(" - <bold>".$label."</bold> : ");
            try {
                $projectGrantService->addNewPoleCompetivite($label);
                $output->write("<ok>Fait</ok>");
            } catch (\Exception $e) {
                $output->write("<error>Erreur : ".$e->getMessage()."</error>");
            }
            $output->write("\n");
        }
    }
}