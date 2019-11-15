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

class OscarCommandExampleCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'dev:commandexample';

    protected function configure()
    {
        $this
            ->setDescription("Petit exemple de commande OSCAR")
            ->setHelp("Fichier d'exemple pour créer vos propres commandes")
            ->addArgument('argumentobligatoire', InputArgument::REQUIRED, "L'argument toto est requis")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $oscarConfigurationService = $this->getServicemanager()->get(OscarConfigurationService::class);
        $output->writeln($oscarConfigurationService->getVersion());

    }
}