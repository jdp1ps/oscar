<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 16:37
 */

namespace Oscar\Factory;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class OscarServiceFactory implements AbstractFactoryInterface
{

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        die($requestedName);
        //
        // TODO: Implement canCreate() method.
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // TODO: Implement __invoke() method.
    }


}