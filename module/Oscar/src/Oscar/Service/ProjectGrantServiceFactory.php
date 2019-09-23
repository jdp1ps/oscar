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

class ProjectGrantServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new ProjectGrantService();
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $s->setEntityManager($container->get(EntityManager::class));
        $s->setLoggerService($container->get('Logger'));
        $s->setOscarUserContextService($container->get(OscarUserContext::class));
        $s->setPersonService($container->get(PersonService::class));
        $s->setOrganizationService($container->get(OrganizationService::class));
        return $s;
    }
}