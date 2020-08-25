<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 24/09/19
 * Time: 09:58
 */

namespace Oscar\Service;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Oscar\Exception\OscarException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class LoggerServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $logPath = $container->get(OscarConfigurationService::class)->getConfiguration('log_path');
        $logLevel = $container->get(OscarConfigurationService::class)->getConfiguration('log_level');

        if( !file_exists($logPath) ){
            $handler = fopen($logPath, 'w');
            if( !$handler ){
              throw new OscarException("Impossible de créer le fichier de LOG !");
            }
            fwrite($handler, "");
            fclose($handler);
        }

        if( !is_writable($logPath) ){
            throw new OscarException("Le fichier de log n'est pas accessible en écriture");
        }

        $logger = new \Monolog\Logger('oscar');
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($logPath, $logLevel));
        return $logger;
    }
}
