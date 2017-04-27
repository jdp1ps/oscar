<?php

namespace UnicaenAuth\Authentication;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Authentication\AuthenticationService;

/**
 * Description of AuthenticationServiceFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return AuthenticationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AuthenticationService(
            $serviceLocator->get('UnicaenAuth\Authentication\Storage\Chain'),
            $serviceLocator->get('ZfcUser\Authentication\Adapter\AdapterChain')
        );
    }
}