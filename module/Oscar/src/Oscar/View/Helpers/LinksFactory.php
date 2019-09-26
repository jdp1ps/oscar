<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 26/09/19
 * Time: 17:29
 */

namespace Oscar\View\Helpers;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class LinksFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $link = new Links();
        $link->setServiceLocator($container);
        return $link;

    }

}