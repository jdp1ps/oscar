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
use Laminas\ServiceManager\Factory\FactoryInterface;
use UnicaenAuth\Service\UserContext;

class OscarUserContextFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $oscarUserContext = new OscarUserContext();
        $oscarUserContext->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $oscarUserContext->setUserContext($container->get(\UnicaenAuthentification\Service\UserContext::class));
        $oscarUserContext->setServiceContainer($container);
        $oscarUserContext->setEntityManager($container->get(EntityManager::class));
        $oscarUserContext->setLoggerService($container->get("Logger"));

        return $oscarUserContext;
    }
}