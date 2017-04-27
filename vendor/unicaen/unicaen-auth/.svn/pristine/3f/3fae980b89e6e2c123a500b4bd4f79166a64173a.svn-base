<?php

namespace UnicaenAuth\Provider\Identity;

use UnicaenApp\Options\ModuleOptions;
use Zend\Ldap\Ldap;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Db identity provider factory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class DbServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $user             = $serviceLocator->get('zfcuser_user_service');
        $identityProvider = new Db($user->getAuthService());

        $unicaenAppOptions = $serviceLocator->get('unicaen-app_module_options');
        /* @var $unicaenAppOptions ModuleOptions */

        $ldap = new Ldap($unicaenAppOptions->getLdap()['connection']['default']['params']);
        $identityProvider->setLdap($ldap);

        $identityProvider->setServiceRole($serviceLocator->get('UnicaenAuth\Service\Role'));

        $config            = $serviceLocator->get('BjyAuthorize\Config');
        $identityProvider->setDefaultRole($config['default_role']);
        $identityProvider->setAuthenticatedRole($config['authenticated_role']);

        return $identityProvider;
    }
}