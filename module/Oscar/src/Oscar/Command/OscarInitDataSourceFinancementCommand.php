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

class OscarInitDataSourceFinancementCommand extends OscarJsonInitDataLoaderCommandAbstract
{
    protected static $defaultName = 'initdata:pcru-source-financement';

    protected function configure()
    {
        $this
            ->setDescription("Charge les sources de financements par défaut (officiel CNRS)")
            ->setHelp("")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $output->writeln("<title>### CHARGEMENT des SOURCES de FINANCEMENT (PCRU) ###</title>");
        $jsonSource = __DIR__ . "/../../../../../install/pcru-sources-financement.json";
        $output->writeln("Chargement depuis <bold>".realpath($jsonSource)."</bold>");
        $output->writeln("...");

        $datas = json_decode(file_get_contents($jsonSource));

        /** @var ProjectGrantService $projectGrantService */
        $projectGrantService = $this->getServicemanager()->get(ProjectGrantService::class);

        foreach ($datas as $label) {
            $output->write(" - <bold>".$label."</bold> : ");
            try {
                $projectGrantService->addNewSourceFinancement($label);
                $output->write("<fg=green>Fait</>");
            } catch (\Exception $e) {
                $output->write("<error>Erreur : ".$e->getMessage()."</error>");
            }
            $output->write("\n");
        }
    }
}