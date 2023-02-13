<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 19/09/19
 * Time: 18:36
 */

namespace Oscar\View\Helpers;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Utils\ViteUtils;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class ViteFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new Vite();
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        return $s;
    }
}