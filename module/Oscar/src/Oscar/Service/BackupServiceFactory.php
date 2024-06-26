<?php
namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class BackupServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new BackupService();
        $s->setEntityManager($container->get(EntityManager::class));
        $s->setLoggerService($container->get('Logger'));
        $s->setServiceContainer($container);
        return $s;
    }
}