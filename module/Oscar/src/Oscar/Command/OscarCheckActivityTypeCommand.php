<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Service\ActivityTypeService;
use Oscar\Service\OscarUserContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarCheckActivityTypeCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'check:activitytype';

    protected function configure()
    {
        $this
            ->setDescription("Vérification du mailer")
            ->addOption('fix', 'f', InputOption::VALUE_NONE, 'Appliquer un recalcule des bornes de l\'arbre')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        $fix = $input->getOption('fix');


        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Vérification de l'intégrité des types d'activité");

        try {
            /** @var ActivityTypeService $activityTypeService */
            $activityTypeService = $this->getServicemanager()->get(ActivityTypeService::class);

            $results = $activityTypeService->verify($fix);
            $headers = ['ID', 'statut', 'label', 'Expected', 'Infos', 'Opens'];
            $rows = [];
            foreach ($results['details'] as $r) {
                $rows[] = [
                  $r['id'],
                  $r['status'] ? 'ok' : 'ERROR',
                  $r['label'],
                  $r['expected'],
                  $r['infos'],
                  $r['opens'],
                ];
            }
            $io->table($headers, $rows);

            foreach ($results['errors'] as $r) {
                $io->warning($r);
            }

            if( $results['needfix'] ){
                $io->warning("Necessite un correctif avec l'option --fix");
                return self::FAILURE;
            } else {
                $io->success("L'arbre des types est cohérent");
                return self::SUCCESS;
            }

        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return self::FAILURE;
        }
    }
}