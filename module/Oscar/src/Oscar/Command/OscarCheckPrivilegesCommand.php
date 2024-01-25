<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;

use Oscar\Service\MaintenanceService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarCheckPrivilegesCommand extends OscarAdvancedCommandAbstract
{
    protected static $defaultName = 'check:privileges';

    protected function configure()
    {
        $this
            ->setDescription("Vérification et mise à jour des privilèges")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) :int
    {
        parent::execute($input, $output);
        try {
            /** @var MaintenanceService $maintenanceService */
            $maintenanceService = $this->getServicemanager()->get(MaintenanceService::class);
            $force = $this->isForce();
            $io = new SymfonyStyle($input, $output);
            $io->title("Synchronisation des privilèges");
            $todo = $maintenanceService->privilegesCheckUpdate($io, $this->isNoInteraction());

            return 0;
        } catch (\Exception $e) {
            $this->finalFatalError($e);
            return 1;
        }
    }
}