<?php

namespace UnicaenLdap\Options;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of ModuleOptionsFactory
 *
 * @author Laurent LECLUSE <laurent.lecluse at unicaen.fr>
 */
class ModuleOptionsFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config       = $serviceLocator->get('Configuration');
        $moduleConfig = isset($config['unicaen-ldap']) ? $config['unicaen-ldap'] : array();
        return new ModuleOptions($moduleConfig);
    }
}
