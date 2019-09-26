<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 26/09/19
 * Time: 13:16
 */

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class ActivityLogServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new ActivityLogService();
        $s->setOscarUserContextService($container->get(OscarUserContext::class));
        $s->setEntityManager($container->get(EntityManager::class));
        return $s;
    }


}