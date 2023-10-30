<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 19/09/19
 * Time: 18:36
 */

namespace Oscar\View\Helpers;


use Interop\Container\ContainerInterface;
use Oscar\Service\OscarUserContext;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GrantFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new Grant();
        $ouc = $container->get(OscarUserContext::class);
        $s->setOscarUserContextService($ouc);
        return $s;
    }
}