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

class TypeDocumentServiceFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new TypeDocumentService();
        $this->init($s, $container);
        return $s;
    }
}