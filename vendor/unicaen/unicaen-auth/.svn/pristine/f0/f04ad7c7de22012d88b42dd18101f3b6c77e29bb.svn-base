<?php

namespace UnicaenAuth\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of UserProfileFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserProfileFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $helperPluginManager
     * @return UserProfile
     */
    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        $serviceLocator  = $helperPluginManager->getServiceLocator();
        $authUserContext = $serviceLocator->get('authUserContext');

        return new UserProfile($authUserContext);
    }
}