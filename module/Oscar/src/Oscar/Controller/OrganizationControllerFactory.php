<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 25/09/19
 * Time: 09:06
 */

namespace Oscar\Controller;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Monolog\Logger;
use Oscar\Service\ActivityLogService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\ProjectService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class OrganizationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new OrganizationController();
        $c->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $c->setLoggerService($container->get('Logger'));
        $c->setEntityManager($container->get(EntityManager::class));
        $c->setOrganizationService($container->get(OrganizationService::class));
        $c->setProjectService($container->get(ProjectService::class));
        $c->setProjectGrantService($container->get(ProjectGrantService::class));
        $c->setActivityLogService($container->get(ActivityLogService::class));
        return $c;
    }
}