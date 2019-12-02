<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 01/10/19
 * Time: 14:28
 */

namespace Oscar\Controller;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Oscar\Service\ActivityLogService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\ProjectService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class ProjectControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new ProjectController();
        $c->setProjectService($container->get(ProjectService::class));
        $c->setEntityManager($container->get(EntityManager::class));
        $c->setOscarUserContextService($container->get(OscarUserContext::class));
        $c->setActivityLogService($container->get(ActivityLogService::class));
        $c->setProjectGrantService($container->get(ProjectGrantService::class));
        return $c;
    }

}