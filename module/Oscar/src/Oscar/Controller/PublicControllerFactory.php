<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:52
 */

namespace Oscar\Controller;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Oscar\Entity\ActivityRequest;
use Oscar\Factory\AbstractOscarFactory;
use Oscar\Service\ActivityRequestService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\TimesheetService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class PublicControllerFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new PublicController();
        $this->init($c, $container);
        $c->setActivityRequestService($container->get(ActivityRequestService::class));
        return $c;
    }

}