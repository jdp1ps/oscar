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
use Interop\Container\Exception\ContainerException;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class AdministrationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new AdministrationController();
        $c->setOscarUserContextService($container->get(OscarUserContext::class));
        $c->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $c->setEntityManager($container->get(EntityManager::class));
        return $c;
    }

}