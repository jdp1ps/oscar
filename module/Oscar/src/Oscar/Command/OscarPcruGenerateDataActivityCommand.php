<?php


namespace Oscar\Command;


use Oscar\Entity\Activity;
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
            ->setDescription("Génération des données PCRU à partir d'une activités.")
            ->addArgument('oscarid', InputOption::VALUE_REQUIRED, 'N° Oscar')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarConfigurationService $configuration */
        $configuration = $this->getServicemanager()->get(OscarConfigurationService::class);

        /** @var PCRUService $pcruService */
        $pcruService = $this->getServicemanager()->get(PCRUService::class);

        /** @var ProjectGrantService $activityService */
        $activityService = $this->getServicemanager()->get(ProjectGrantService::class);

        /** @var SymfonyStyle $io */
        $io = new SymfonyStyle($input, $output);


        // Récupération des données
        $numeroOscar = $input->getArgument('oscarid');

        $io->write("Récupération de l'activité '$numeroOscar' : ");
        try {
            /** @var Activity $activity */
            $activity = $activityService->getEntityManager()->getRepository(Activity::class)->findOneBy(['oscarNum' => $numeroOscar]);
            if( !$activity ) {
                throw new \Exception("Aucune activité avec le numéro $numeroOscar");
            }
            $io->writeln("<fg=green>$activity</fg=green>");
            if( $activity->getPcruInfo() ){
                $io->error("Cette activité a déjà des données PCRU en court d'édition, passez par l'interface pour les modifier.");
            } else {
                $io->title("Génération des données PCRU...");
                $pcruInfoFactory = new ActivityPcruInfoFromActivityFactory($configuration, $activityService->getEntityManager());
                $pcruInfos = $pcruInfoFactory->createNew($activity);
                $table = new Table($io);
                $table->setHeaders(["Colonne", 'Champ', 'Valeur PCRU']);
                $num = 1;
                foreach( $pcruInfos->toArray() as $key=>$value ){
                    $table->addRow([$num++, "$key", "$value"]);
                }
                $table->render();
            }
        } catch (\Exception $e) {
            $io->write('<error>'. $e->getMessage() . '</error>');
            $io->writeln("");
            return 0;
        }



       return 0;
    }
}