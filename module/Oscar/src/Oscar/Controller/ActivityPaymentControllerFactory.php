<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 27/09/19
 * Time: 14:09
 */

namespace Oscar\Controller;


use Interop\Container\ContainerInterface;
use Oscar\Service\ActivityLogService;
use Oscar\Service\NotificationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ActivityPaymentControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new ActivityPaymentController();
        $c->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $c->setLoggerService($container->get('Logger'));
        $c->setActivityLogService($container->get(ActivityLogService::class));
        $c->setOscarUserContextService($container->get(OscarUserContext::class));
        $c->setProjectGrantService($container->get(ProjectGrantService::class));
        $c->setNotificationService($container->get(NotificationService::class));
        $c->setPersonService($container->get(PersonService::class));
        return $c;
    }

}