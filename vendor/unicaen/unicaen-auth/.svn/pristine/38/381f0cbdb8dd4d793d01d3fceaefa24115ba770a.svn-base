<?php

namespace UnicaenAuth\View\Helper;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of UserProfileFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserProfileSelectFactory extends \UnicaenApp\View\Helper\UserProfileSelectFactory
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

        return new UserProfileSelect($userContextService);
    }
}