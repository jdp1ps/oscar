<?php

namespace UnicaenAuth\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of UserConnectionFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserConnectionFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $helperPluginManager
     * @return UserConnection
     */
    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        $authUserContext = $helperPluginManager->getServiceLocator()->get('authUserContext');
        
        return new UserConnection($authUserContext);
    }
}