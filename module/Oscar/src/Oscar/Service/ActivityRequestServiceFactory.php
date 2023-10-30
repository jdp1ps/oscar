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

class ActivityRequestServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new ActivityRequestService();

        $s->setEntityManager($container->get(EntityManager::class));
        $s->setPersonService($container->get(PersonService::class));
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $s->setNotificationService($container->get(NotificationService::class));
        $s->setProjectGrantService($container->get(ProjectGrantService::class));
        $s->setLoggerService($container->get('Logger'));

        return $s;
    }
}