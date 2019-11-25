<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 25/09/19
 * Time: 13:44
 */

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Oscar\Factory\AbstractOscarFactory;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class MaintenanceServiceFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new MaintenanceService();
        $this->init($s, $container);
        return $s;
    }
}