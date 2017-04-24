<?php

namespace UnicaenAuth\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of UserStatusFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserStatusFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $helperPluginManager
     * @return UserStatus
     */
    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        $userContext = $helperPluginManager->getServiceLocator()->get('authUserContext');
        
        return new UserStatus($userContext);
    }
}