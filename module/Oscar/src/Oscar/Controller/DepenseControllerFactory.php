<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 27/09/19
 * Time: 13:44
 */

namespace Oscar\Controller;


use Interop\Container\ContainerInterface;
use Oscar\Factory\AbstractOscarFactory;

class DepenseControllerFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new DepenseController();
        $c->setServiceContainer($container);
        return $c;
    }
}