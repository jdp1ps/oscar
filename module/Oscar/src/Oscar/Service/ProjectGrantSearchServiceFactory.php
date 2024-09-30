<?php

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ProjectGrantSearchServiceFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): ProjectGrantSearchService {
        $s = new ProjectGrantSearchService();

        $s->setEntityManager($container->get(EntityManager::class));
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $s->setProjectGrantService($container->get(ProjectGrantService::class));
        $s->setSpentService($container->get(SpentService::class));
        $s->setLoggerService($container->get('Logger'));

        return $s;
    }
}