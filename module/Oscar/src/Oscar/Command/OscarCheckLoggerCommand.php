<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Monolog\Logger;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarCheckLoggerCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'check:logger';

    protected function configure()
    {
        $this
            ->setDescription("Vérification des logs")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Vérification du logger");

        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);


        /** @var Logger $logger */
        $logger = $this->getServicemanager()->get('Logger');

        $msg = "Niveau DEBUG loggué";
        $io->text("Envoi : <bold>$msg</bold> dans les logs");
        $logger->debug($msg);

        $msg = "Niveau INFO loggué";
        $io->text("Envoi : <bold>$msg</bold> dans les logs");
        $logger->info($msg);

        $msg = "Niveau NOTICE loggué";
        $io->text("Envoi : <bold>$msg</bold> dans les logs");
        $logger->notice($msg);

        $msg = "Niveau WARNING loggué";
        $io->text("Envoi : <bold>$msg</bold> dans les logs");
        $logger->warning($msg);

        $msg = "Niveau ALERT loggué";
        $io->text("Envoi : <bold>$msg</bold> dans les logs");
        $logger->alert($msg);

        $msg = "Niveau ERROR loggué";
        $io->text("Envoi : <bold>$msg</bold> dans les logs");
        $logger->error($msg);

        $msg = "Niveau CRITICAL loggué";
        $io->text("Envoi : <bold>$msg</bold> dans les logs");
        $logger->critical($msg);

        $logFile = realpath($oscarConfig->getLoggerFilePath());
        $io->note("Vérifier le fichier '$logFile' : tail -f $logFile");
    }
}