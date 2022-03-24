<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 19/09/19
 * Time: 18:45
 */

namespace Oscar\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class NotificationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new NotificationService();
        $s->setServiceContainer($container);
        return $s;
    }
}