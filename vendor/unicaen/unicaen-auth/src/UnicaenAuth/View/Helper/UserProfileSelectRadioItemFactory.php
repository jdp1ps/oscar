<?php

namespace UnicaenAuth\View\Helper;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserProfileSelectRadioItemFactory extends \UnicaenApp\View\Helper\UserProfileSelectFactory
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $helperPluginManager
     * @return UserProfile
     */
    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        $serviceLocator     = $helperPluginManager->getServiceLocator();
        $userContextService = $serviceLocator->get('AuthUserContext');

        return new UserProfileSelectRadioItem($userContextService);
    }
}