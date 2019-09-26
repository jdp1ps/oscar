<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 26/09/19
 * Time: 13:58
 */

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class UserParametersServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new UserParametersService();
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $s->setActivityLogService($container->get(ActivityLogService::class));
        $s->setEntityManager($container->get(EntityManager::class));
        return $s;
    }
}