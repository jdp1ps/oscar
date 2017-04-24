<?php

namespace UnicaenAuth\Provider\Identity;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Basic identity provider factory.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class BasicServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $user              = $serviceLocator->get('zfcuser_user_service');
        $config            = $serviceLocator->get('BjyAuthorize\Config');
        $identityProvider  = new Basic($user->getAuthService());

        $identityProvider->setDefaultRole($config['default_role']);
        $identityProvider->setAuthenticatedRole($config['authenticated_role']);

        return $identityProvider;
    }
}