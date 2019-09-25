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
use Oscar\Formatter\TimesheetActivityPeriodHtmlFormatter;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\TimesheetService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class TimesheetControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new TimesheetController();

        $c->setOscarUserContextService($container->get(OscarUserContext::class));
        $c->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $c->setEntityManager($container->get(EntityManager::class));
        $c->setLoggerService($container->get('Logger'));
        $c->setTimesheetService($container->get(TimesheetService::class));
        $c->setPersonService($container->get(PersonService::class));
        $c->setViewRenderer($container->get('ViewRenderer'));
        return $c;
    }

}