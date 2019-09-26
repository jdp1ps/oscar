<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/11/15 11:49
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Helpers;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Oscar\Service\ActivityTypeService;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\View\Helper\AbstractHtmlElement;

class ActivityTypeHelperFactory implements FactoryInterface {
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $o = new ActivityTypeHelper();
        $o->setServiceLocator($container);
        return $o;
    }

}