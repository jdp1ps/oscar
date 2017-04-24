<?php

namespace UnicaenAuth\Provider\Role;

use BjyAuthorize\Exception\InvalidArgumentException;
use UnicaenAuth\Service\RoleService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible of instantiating {@see \UnicaenAuth\Provider\Role\DbRole}
 */
class DbRoleServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return DbRole
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceRole = $serviceLocator->get('UnicaenAuth\Service\Role');
        /* @var $serviceRole RoleService */

        return new DbRole($serviceRole->getRepo());
    }
}
