<?php

namespace UnicaenAuth\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of UserInfoFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserInfoFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $helperPluginManager
     * @return UserInfo
     */
    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        $serviceLocator  = $helperPluginManager->getServiceLocator();
        $authUserContext = $serviceLocator->get('authUserContext');
        $mapper          = $serviceLocator->get('ldap_structure_mapper');

        $helper = new UserInfo($authUserContext);
        $helper->setMapperStructure($mapper);
        
        return $helper;
    }
}