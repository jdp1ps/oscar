<?php

namespace UnicaenAuth\Provider\Identity;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use UnicaenAuth\Acl\NamedRole;

/**
 * LDAP identity provider factory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class LdapServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config                 = $serviceLocator->get('BjyAuthorize\Config');
        $user                   = $serviceLocator->get('zfcuser_user_service');
        $simpleIdentityProvider = new Ldap($user->getAuthService());
        
        $simpleIdentityProvider->setDefaultRole($config['default_role']);
        $simpleIdentityProvider->setAuthenticatedRole($config['authenticated_role']);

        return $simpleIdentityProvider;
    }
}