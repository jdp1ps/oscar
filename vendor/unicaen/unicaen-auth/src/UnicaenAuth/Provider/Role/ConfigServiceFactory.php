<?php

namespace UnicaenAuth\Provider\Role;

use BjyAuthorize\Exception\InvalidArgumentException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible of instantiating {@see \UnicaenAuth\Provider\Role\Config}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ConfigServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \BjyAuthorize\Provider\Role\Config
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('BjyAuthorize\Config');

        if (! isset($config['role_providers']['UnicaenAuth\Provider\Role\Config'])) {
            throw new InvalidArgumentException(
                'Config for "UnicaenAuth\Provider\Role\Config" not set'
            );
        }
        
        $providerConfig = $config['role_providers']['UnicaenAuth\Provider\Role\Config'];
        
        /* @var $mapper \UnicaenApp\Mapper\Ldap\Group */
        $mapper = $serviceLocator->get('ldap_group_mapper');

        $service = new Config($mapper, $providerConfig);

        return $service;
    }
}
