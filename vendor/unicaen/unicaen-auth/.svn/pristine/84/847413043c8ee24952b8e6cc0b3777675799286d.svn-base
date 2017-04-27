<?php

namespace UnicaenAuth\Options\Traits;

use UnicaenAuth\Options\ModuleOptions;
use RuntimeException;

/**
 * Description of ModuleOptionsAwareTrait
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
trait ModuleOptionsAwareTrait
{
    /**
     * @var ModuleOptions
     */
    private $moduleOptions;



    /**
     * @param ModuleOptions $moduleOptions
     *
     * @return self
     */
    public function setModuleOptions(ModuleOptions $moduleOptions)
    {
        $this->moduleOptions = $moduleOptions;

        return $this;
    }



    /**
     * @return ModuleOptions
     * @throws RuntimeException
     */
    public function getModuleOptions()
    {
        if (empty($this->moduleOptions)) {
            if (!method_exists($this, 'getServiceLocator')) {
                throw new RuntimeException('La classe ' . get_class($this) . ' n\'a pas accès au ServiceLocator.');
            }

            $serviceLocator = $this->getServiceLocator();
            if (method_exists($serviceLocator, 'getServiceLocator')) {
                $serviceLocator = $serviceLocator->getServiceLocator();
            }
            $this->moduleOptions = $serviceLocator->get('unicaen-auth_module_options');
        }

        return $this->moduleOptions;
    }
}