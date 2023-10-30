<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 19/09/19
 * Time: 18:36
 */

namespace Oscar\View\Helpers;


use Interop\Container\ContainerInterface;
use Oscar\Service\OscarConfigurationService;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ViteFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new Vite();
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        return $s;
    }
}