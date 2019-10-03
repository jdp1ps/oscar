<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 02/10/19
 * Time: 10:06
 */

namespace Oscar\Factory;


use Doctrine\ORM\EntityManager;
use Oscar\Service\ActivityLogService;
use Oscar\Service\NotificationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\ProjectService;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UseProjectGrantService;
use Oscar\Traits\UseProjectService;
use Psr\Container\ContainerInterface;

abstract class AbstractOscarFactory
{

    protected function init( $service, ContainerInterface $container )
    {

        // Abstract Oscar Controller
        if( class_implements($service, UseEntityManager::class) ){
            $service->setEntityManager($container->get(EntityManager::class));
        }

        if( class_implements($service, UseOscarConfigurationService::class) ){
            $service->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        }

        if( class_implements($service, UseLoggerService::class) ){
            $service->setLoggerService($container->get('Logger'));
        }

        if( class_implements($service, UseActivityLogService::class) ){
            $service->setActivityLogService($container->get(ActivityLogService::class));
        }

        if( class_implements($service, UseOscarUserContextService::class) ){
            $service->setOscarUserContextService($container->get(OscarUserContext::class));
        }

        // SERVICE MÉTIER
        if( class_implements($service, UsePersonService::class) ){
            $service->setPersonService($container->get(PersonService::class));
        }

        if( class_implements($service, UseProjectService::class) ){
            $service->setProjectService($container->get(ProjectService::class));
        }

        if( class_implements($service, UseProjectGrantService::class) ){
            $service->setProjectGrantService($container->get(ProjectGrantService::class));
        }

        // NOTIFICATION
        if( class_implements($service, UseNotificationService::class) ){
            $service->setNotificationService($container->get(NotificationService::class));
        }

        // LOGGER

        return $service;
    }

}