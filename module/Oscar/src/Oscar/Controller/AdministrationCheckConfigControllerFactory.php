<?php

namespace Oscar\Controller;

use Interop\Container\ContainerInterface;
use Oscar\Factory\AbstractOscarFactory;
use Oscar\Service\administration\CheckConfigService;

class AdministrationCheckConfigControllerFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new AdministrationCheckConfigController();
        $c->setServiceLocator($container);
        $c->setCheckConfigService($container->get(CheckConfigService::class));
        $this->init($c, $container);
        return $c;
    }
}
