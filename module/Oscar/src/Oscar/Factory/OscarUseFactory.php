<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 16:37
 */

namespace Oscar\Factory;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
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
use Oscar\Traits\UseNotificationService;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UseProjectGrantService;
use Oscar\Traits\UseProjectService;
use Oscar\Traits\UseServiceContainer;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class OscarUseFactory implements AbstractFactoryInterface
{

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return true;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $instance = $options ? new $requestedName($options) : new $requestedName;
        $instance = $this->init($instance, $container);
        return $instance;
    }

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

        // NOTIFICATION
        if( class_implements($service, UseServiceContainer::class) ){
            $service->setServiceContainer($container);
        }

        // LOGGER

        return $service;
    }


}