<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 25/09/19
 * Time: 13:44
 */

namespace Oscar\Service;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class MilestoneServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new MilestoneService();
        $s->setServiceLocator($container);
        return $s;
    }
}