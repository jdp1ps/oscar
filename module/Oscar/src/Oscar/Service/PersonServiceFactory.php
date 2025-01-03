<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 19/09/19
 * Time: 18:45
 */

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Oscar\Factory\AbstractOscarFactory;
use Oscar\Traits\UseNotificationService;
use Oscar\Traits\UseOscarUserContextService;

class PersonServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new PersonService();
        $s->setServiceContainer($container);
        $s->setActivityLogService($container->get(ActivityLogService::class));
        $s->setOscarUserContextService($container->get(OscarUserContext::class));
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $s->setEntityManager($container->get(EntityManager::class));
        $s->setLoggerService($container->get('Logger'));
        $s->setNotificationService($container->get(NotificationService::class));
        $s->setGearmanJobLauncherService($container->get(GearmanJobLauncherService::class));
        return $s;
    }
}