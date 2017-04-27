<?php

namespace UnicaenAuth\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of UserCurrentFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserCurrentFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $helperPluginManager
     * @return UserCurrent
     */
    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        $authUserContext = $helperPluginManager->getServiceLocator()->get('authUserContext');
        
        return new UserCurrent($authUserContext);
    }
}