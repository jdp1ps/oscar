<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 26/09/19
 * Time: 10:49
 */

namespace Oscar\Controller;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Oscar\Service\ActivityLogService;
use Oscar\Service\NotificationService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\ProjectService;
use Laminas\ServiceManager\Factory\FactoryInterface;

class EnrollControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new EnrollController();
        $c->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $c->setOscarUserContextService($container->get(OscarUserContext::class));
        $c->setEntityManager($container->get(EntityManager::class));
        $c->setLoggerService($container->get('Logger'));

        $c->setPersonService($container->get(PersonService::class));
        $c->setActivityService($container->get(ProjectGrantService::class));
        $c->setProjectService($container->get(ProjectService::class));
        $c->setProjectGrantService($container->get(ProjectGrantService::class));
        $c->setActivityLogService($container->get(ActivityLogService::class));
        $c->setNotificationService($container->get(NotificationService::class));
        $c->setOrganizationService($container->get(OrganizationService::class));
        return $c;
    }

}