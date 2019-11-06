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
use Oscar\Service\ActivityTypeService;
use Oscar\Service\AdministrativeDocumentService;
use Oscar\Service\NotificationService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\ProjectService;
use Oscar\Service\TimesheetService;
use Oscar\Service\TypeDocumentService;
use Oscar\Service\UserParametersService;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityTypeService;
use Oscar\Traits\UseAdministrativeDocumentService;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseOrganizationService;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UseProjectGrantService;
use Oscar\Traits\UseProjectService;
use Oscar\Traits\UseTimesheetService;
use Oscar\Traits\UseTypeDocumentService;
use Oscar\Traits\UseUserParametersService;
use Psr\Container\ContainerInterface;

abstract class AbstractOscarFactory
{

    protected function init( $service, ContainerInterface $container )
    {

        // Abstract Oscar Controller
        if( is_subclass_of($service, UseEntityManager::class) ){
            $service->setEntityManager($container->get(EntityManager::class));
        }

        if( is_subclass_of($service, UseOscarConfigurationService::class) ){
            $service->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        }

        if( is_subclass_of($service, UseLoggerService::class) ){
            $service->setLoggerService($container->get('Logger'));
        }

        if( is_subclass_of($service, UseActivityLogService::class) ){
            $service->setActivityLogService($container->get(ActivityLogService::class));
        }

        if( is_subclass_of($service, UseOscarUserContextService::class) ){
            $service->setOscarUserContextService($container->get(OscarUserContext::class));
        }

        // -------------------------------------------------------------------------------------------------------------
        // SERVICE MÉTIER
        if( is_subclass_of($service, UseActivityTypeService::class) ){
            $service->setActivityTypeService($container->get(ActivityTypeService::class));
        }
        if( is_subclass_of($service, UsePersonService::class) ){
            $service->setPersonService($container->get(PersonService::class));
        }

        if( is_subclass_of($service, UseProjectService::class) ){
            $service->setProjectService($container->get(ProjectService::class));
        }

        if( is_subclass_of($service, UseProjectGrantService::class) ){
            $service->setProjectGrantService($container->get(ProjectGrantService::class));
        }

        if( is_subclass_of($service, UseTypeDocumentService::class) ){
            $service->setTypeDocumentService($container->get(TypeDocumentService::class));
        }

        if( is_subclass_of($service, UseAdministrativeDocumentService::class) ){
            $service->setAdministrativeDocumentService($container->get(AdministrativeDocumentService::class));
        }

        if( is_subclass_of($service, UseOrganizationService::class) ){
            $service->setOrganizationService($container->get(OrganizationService::class));
        }

        if( is_subclass_of($service, UseTimesheetService::class)) {
            $service->setTimesheetService($container->get(TimesheetService::class));
        }

        // NOTIFICATION
        if( is_subclass_of($service, UseNotificationService::class) ){
            $service->setNotificationService($container->get(NotificationService::class));
        }

        if( is_subclass_of($service, UseUserParametersService::class) ){
            $service->setUserParametersService($container->get(UserParametersService::class));
        }



        // LOGGER

        return $service;
    }

}