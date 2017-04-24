<?php

namespace UnicaenAuth\Provider\Role;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of UsernameServiceFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UsernameServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Username
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('BjyAuthorize\Config');

        if (! isset($config['role_providers']['UnicaenAuth\Provider\Role\Username'])) {
            throw new InvalidArgumentException(
                'Config for "UnicaenAuth\Provider\Role\Username" not set'
            );
        }

        $providerConfig = $config['role_providers']['UnicaenAuth\Provider\Role\Username'];
        
        $authService = $serviceLocator->get('zfcuser_auth_service'); /* @var $authService \Zend\Authentication\AuthenticationService */
        
        return new Username($authService, $providerConfig);
    }
}