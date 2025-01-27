<?php

namespace Oscar\Service;

use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ActivityAvenantsServiceFactory implements FactoryInterface
{
    public function __invoke(\Psr\Container\ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new ActivityAvenantsService();
        $s->setEntityManager($container->get(EntityManager::class));
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $s->setLoggerService($container->get('Logger'));
        return $s;
    }
}