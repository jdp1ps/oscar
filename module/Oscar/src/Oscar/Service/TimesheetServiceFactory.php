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

class TimesheetServiceFactory extends OscarServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new TimesheetService();
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $s->setEntityManager($container->get(EntityManager::class));
        $s->setLoggerService($container->get('Logger'));
        $s->setOscarUserContextService($container->get(OscarUserContext::class));
        $s->setPersonService($container->get(PersonService::class));
        $s->setOrganizationService($container->get(OrganizationService::class));
        $s->setActivityService($container->get(ProjectGrantService::class));
        $s->setNotificationService($container->get(NotificationService::class));
        $s->setActivityLogService($container->get(ActivityLogService::class));
        $s->setViewRenderer($container->get('ViewRenderer'));
        return $s;
    }
}