<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 26/09/19
 * Time: 13:16
 */

namespace Oscar\Service;


use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Oscar\Factory\AbstractOscarFactory;

class ActivityPaymentServiceFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new ActivityPaymentService();
        $this->init($s, $container);
        return $s;
    }


}