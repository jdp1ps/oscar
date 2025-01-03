<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 24/09/19
 * Time: 12:52
 */

namespace Oscar\Service;


use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ConnectorServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new ConnectorService();
        $service->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $service->setServiceManager($container);
        return $service;
    }
}