<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Service\OscarConfigurationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\ServiceManager\ServiceManager;

class OscarDebugCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'oscar:debug';

    protected function configure()
    {
        $this
            ->setDescription("Debug d'oscar")
            ->setHelp("Lance le diagnostique de l'application Oscar")
            ->addArgument('toto', InputArgument::REQUIRED, "L'argument toto est requis")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var OscarConfigurationService $oscarConfigurationService */
        $oscarConfigurationService = $this->sm->get(OscarConfigurationService::class);
        $output->writeln($oscarConfigurationService->getTheme());

    }
}