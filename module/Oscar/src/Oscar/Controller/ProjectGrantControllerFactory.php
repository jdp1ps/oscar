<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:52
 */

namespace Oscar\Controller;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Monolog\Logger;
use Oscar\Service\ActivityLogService;
use Oscar\Service\ActivityRequestService;
use Oscar\Service\ActivityTypeService;
use Oscar\Service\NotificationService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\TimesheetService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class ProjectGrantControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new ProjectGrantController($container->get(TimesheetService::class));

        $c->setLoggerService($container->get('Logger'));
        $c->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $c->setOscarUserContextService($container->get(OscarUserContext::class));
        $c->setEntityManager($container->get(EntityManager::class));
        $c->setLoggerService($container->get('Logger'));
        $c->setActivityRequestService($container->get(ActivityRequestService::class));
        $c->setOrganizationService($container->get(OrganizationService::class));
        $c->setActivityService($container->get(ProjectGrantService::class));
        $c->setActivityTypeService($container->get(ActivityTypeService::class));
        $c->setTimesheetService($container->get(TimesheetService::class));
        $c->setActivityLogService($container->get(ActivityLogService::class));
        $c->setNotificationService($container->get(NotificationService::class));
        return $c;
    }

}