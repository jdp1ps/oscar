<?php
/**
 * Created by PhpStorm.
 * User: marie
 * Date: 17/10/22
 * Time: 14:50
 */

namespace Oscar\Controller;


use Interop\Container\ContainerInterface;
use Oscar\Factory\AbstractOscarFactory;

class TabDocumentControllerFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new TabDocumentController();
        $this->init($c, $container);
        return $c;
    }
}
