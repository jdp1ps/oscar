<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 27/09/19
 * Time: 12:16
 */

namespace Oscar\Controller;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Oscar\Service\ActivityLogService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Laminas\ServiceManager\Factory\FactoryInterface;

class WorkPackageControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new WorkPackageController();
        $c->setActivityLogService($container->get(ActivityLogService::class));
        $c->setEntityManager($container->get(EntityManager::class));
        $c->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $c->setOscarUserContextService($container->get(OscarUserContext::class));
        $c->setLoggerService($container->get('Logger'));
        return $c;
    }
}