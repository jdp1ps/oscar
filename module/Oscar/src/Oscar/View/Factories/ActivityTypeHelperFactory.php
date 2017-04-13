<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/11/15 11:53
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Factories;

use Oscar\View\Helpers\ActivityTypeHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ActivityTypeHelperFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return ActivityTypeHelper
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        var_dump($serviceLocator->get('ActivityType'));
        die();
        return new ActivityTypeHelper($serviceLocator->get('ActivityType'));
    }
}
