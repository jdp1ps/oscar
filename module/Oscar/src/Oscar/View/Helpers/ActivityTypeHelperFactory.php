<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/11/15 11:49
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Helpers;


use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ActivityTypeHelperFactory implements FactoryInterface {
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $o = new ActivityTypeHelper();
        $o->setServiceLocator($container);
        return $o;
    }

}