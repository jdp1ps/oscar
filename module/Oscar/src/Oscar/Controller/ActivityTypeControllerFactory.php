<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 27/09/19
 * Time: 14:09
 */

namespace Oscar\Controller;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Oscar\Factory\AbstractOscarFactory;
use Oscar\Service\ActivityLogService;
use Oscar\Service\NotificationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class ActivityTypeControllerFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new ActivityTypeController();
        $this->init($c, $container);
        return $c;
    }

}