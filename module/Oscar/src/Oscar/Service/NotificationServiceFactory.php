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
use Zend\ServiceManager\Factory\FactoryInterface;

class NotificationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new NotificationService();
        $s->setLoggerService($container->get('Logger'));
        $s->setEntityManager($container->get(EntityManager::class));
        $s->setOrganizationService($container->get(OrganizationService::class));
        $s->setPersonService($container->get(PersonService::class));
        return $s;
    }
}