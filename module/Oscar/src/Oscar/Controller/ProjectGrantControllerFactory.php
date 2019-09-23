<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:52
 */

namespace Oscar\Controller;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Oscar\Service\ActivityRequestService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\TimesheetService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class ProjectGrantControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new PublicController($container->get(TimesheetService::class));

        $c->setOscarUserContextService($container->get(OscarUserContext::class));
        $c->setActivityRequestService($container->get(ActivityRequestService::class));

        $c->setLoggerService($container->get('Logger'));
        return $c;
    }

}