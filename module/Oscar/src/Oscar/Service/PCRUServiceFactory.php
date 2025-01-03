<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 19/09/19
 * Time: 18:45
 */

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PCRUServiceFactory extends OscarServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new PCRUService();
        $s->setServiceContainer($container);
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $s->setEntityManager($container->get(EntityManager::class));
        $s->setLoggerService($container->get('Logger'));
        return $s;
    }
}