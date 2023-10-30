<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 27/09/19
 * Time: 15:50
 */

namespace Oscar\Controller;


use Interop\Container\ContainerInterface;
use Oscar\Service\ActivityLogService;
use Oscar\Service\MilestoneService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\ProjectGrantService;
use Laminas\ServiceManager\Factory\FactoryInterface;
class ActivityDateControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new ActivityDateController();
        $c->setOscarUserContextService($container->get(OscarUserContext::class));
        $c->setProjectGrantService($container->get(ProjectGrantService::class));
        $c->setMilestoneService($container->get(MilestoneService::class));
        $c->setActivityLogService($container->get(ActivityLogService::class));
        return $c;
    }
}