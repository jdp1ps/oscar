<?php

namespace Oscar\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GearmanJobLauncherServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new GearmanJobLauncherService();
        $s->setLoggerService($container->get('Logger'));
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        return $s;
    }
}