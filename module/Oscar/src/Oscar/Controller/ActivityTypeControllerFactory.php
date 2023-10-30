<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 27/09/19
 * Time: 14:09
 */

namespace Oscar\Controller;


use Interop\Container\ContainerInterface;
use Oscar\Factory\AbstractOscarFactory;

class ActivityTypeControllerFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new ActivityTypeController();
        $this->init($c, $container);
        return $c;
    }

}