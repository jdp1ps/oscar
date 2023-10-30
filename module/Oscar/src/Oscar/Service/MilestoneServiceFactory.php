<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 25/09/19
 * Time: 13:44
 */

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class MilestoneServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new MilestoneService();
        $s->setServiceContainer($container);
        $s->setLoggerService($container->get('Logger'));
        $s->setOscarUserContextService($container->get(OscarUserContext::class));
        $s->setEntityManager($container->get(EntityManager::class));
        $s->setNotificationService($container->get(NotificationService::class));
        $s->setActivityLogService($container->get(ActivityLogService::class));
        return $s;
    }
}