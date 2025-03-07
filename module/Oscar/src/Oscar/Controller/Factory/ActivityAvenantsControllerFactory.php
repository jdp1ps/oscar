<?php

namespace Oscar\Controller\Factory;

use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Oscar\Controller\ActivityAvenantsController;
use Oscar\Service\ActivityAvenantsService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\ProjectGrantApiService;
use Psr\Container\ContainerInterface;

class ActivityAvenantsControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new ActivityAvenantsController();
        $c->setActivityAvenantsService($container->get(ActivityAvenantsService::class));
        $c->setProjectGrantApiService($container->get(ProjectGrantApiService::class));
        $c->setEntityManager($container->get(EntityManager::class));
        $c->setOscarUserContextService($container->get(OscarUserContext::class));
        $c->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $c->setLoggerService($container->get('Logger'));
        return $c;
    }
}
