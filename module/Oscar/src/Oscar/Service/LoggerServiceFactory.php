<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 24/09/19
 * Time: 09:58
 */

namespace Oscar\Service;


use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Handler\RotatingFileHandler;
use Oscar\Exception\OscarException;


class LoggerServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // --- CONF : issue de /config/autoload/global.php et /config/autoload/local.php

        /** @var OscarConfigurationService $configurationService */
        $configurationService = $container->get(OscarConfigurationService::class);

        // Emplacement du fichier de log
        $logPath = $configurationService->getLoggerFilePath();

        if (!$logPath) {
            throw new OscarException("Fichier de log mal configuré (Le chemin est vide)");
        }

        // Niveau de log
        $logLevel = $configurationService->getLoggerLevel();


        // Sorties des logs (fichier + PHP stdrout)
        $stream = new RotatingFileHandler($logPath, 5, $logLevel);
        //$stream = new StreamHandler($logPath,$logLevel);
        //$firephp = new FirePHPHandler($logLevel);

        $logger = new LoggerService('oscar');
        $logger->pushHandler($stream);
        //$logger->pushHandler($firephp);

        return $logger;
    }
}
