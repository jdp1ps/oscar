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
use Oscar\Service\ActivityRequestService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\TimesheetService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class PersonControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new PersonController();

        $c->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $c->setOscarUserContextService($container->get(OscarUserContext::class));
        $c->setEntityManager($container->get(EntityManager::class));
        $c->setLoggerService($container->get('Logger'));
        $c->setActivityRequestService($container->get(ActivityRequestService::class));
        $c->setPersonService($container->get(PersonService::class));
        $c->setTimesheetService($container->get(TimesheetService::class));

        $c->setLoggerService($container->get('Logger'));
        return $c;
    }

}