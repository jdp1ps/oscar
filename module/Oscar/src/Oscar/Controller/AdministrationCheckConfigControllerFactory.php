<?php

namespace Oscar\Controller;

use Interop\Container\ContainerInterface;
use Oscar\Factory\AbstractOscarFactory;

class AdministrationCheckConfigControllerFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new AdministrationCheckConfigController();
        $c->setServiceLocator($container);
        $this->init($c, $container);
        return $c;
    }
}
