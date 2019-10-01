<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 01/10/19
 * Time: 16:25
 */

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseNotificationService;
use Oscar\Traits\UseOscarConfigurationService;
use Psr\Container\ContainerInterface;

abstract class AbstractServiceFactory
{
    protected function init( $service, ContainerInterface $container )
    {
        if( class_implements($service, UseEntityManager::class) ){
            $service->setEntityManager($container->get(EntityManager::class));
        }

        if( class_implements($service, UseOscarConfigurationService::class) ){
            $service->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        }

        if( class_implements($service, UseLoggerService::class) ){
            $service->setLoggerService($container->get('Logger'));
        }

        // NOTIFICATION
        if( class_implements($service, UseNotificationService::class) ){
           // $service->setNotificationService($container->get(NotificationService::class));
        }

        // LOGGER

        return $service;
    }

}