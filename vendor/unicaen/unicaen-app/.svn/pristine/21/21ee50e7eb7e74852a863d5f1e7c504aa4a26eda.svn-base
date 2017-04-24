<?php

namespace UnicaenApp\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of UserProfileFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserProfileSelectFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $helperPluginManager
     * @return UserProfile
     */
    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        return new UserProfileSelect();
    }
}