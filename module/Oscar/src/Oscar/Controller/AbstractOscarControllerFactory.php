<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 02/10/19
 * Time: 09:57
 */

namespace Oscar\Controller;


use Oscar\Service\ActivityLogService;
use Oscar\Service\OscarUserContext;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseOscarUserContextService;
use Psr\Container\ContainerInterface;

abstract class AbstractOscarControllerFactory
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

        // NOTIFICATION
        if( class_implements($service, UseNotificationService::class) ){
            // $service->setNotificationService($container->get(NotificationService::class));
        }

        // LOGGER

        return $service;
    }
}