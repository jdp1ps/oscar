<?php

namespace UnicaenAuth\Options;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of ModuleOptionsFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
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
        $moduleConfig = isset($config['unicaen-auth']) ? $config['unicaen-auth'] : [];
        $moduleConfig = array_merge($config['zfcuser'], $moduleConfig);

        return new ModuleOptions($moduleConfig);
    }
}