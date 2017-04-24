<?php

namespace UnicaenApp\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of AppInfosFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AppInfosFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $helperPluginManager
     * @return AppInfos
     */
    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        $options = $helperPluginManager->getServiceLocator()->get('unicaen-app_module_options'); /* @var $options ModuleOptions */
        
        return new AppInfos($options->getAppInfos());
    }
}