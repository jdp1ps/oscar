<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 19/09/19
 * Time: 18:45
 */

namespace Oscar\Service;


use Interop\Container\ContainerInterface;
use Oscar\Factory\AbstractOscarFactory;

class AdministrativeDocumentServiceFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new AdministrativeDocumentService();
        $this->init($s, $container);
        return $s;
    }
}