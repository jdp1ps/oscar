<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 19/09/19
 * Time: 18:30
 */

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Oscar\Exception\OscarException;
use UnicaenAuth\Service\UserContext;
use Zend\ServiceManager\Factory\FactoryInterface;

class OrganizationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $oscarUserContext = new OrganizationService();
        $oscarUserContext->setEntityManager($container->get(EntityManager::class));
        $oscarUserContext->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $oscarUserContext->setOscarUserContextService($container->get(OscarUserContext::class));
        $oscarUserContext->setPersonService($container->get(PersonService::class));
        $oscarUserContext->setLoggerService($container->get("Logger"));

        return $oscarUserContext;
    }
}