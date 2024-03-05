<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 26/09/19
 * Time: 10:49
 */

namespace Oscar\Controller;


use Interop\Container\ContainerInterface;
use Oscar\Factory\AbstractOscarFactory;
use Oscar\Service\ConnectorService;
use UnicaenSignature\Service\SignatureService;

class AdministrationControllerFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new AdministrationController();
        $c->setServiceLocator($container);
        $c->setConnectorService($container->get(ConnectorService::class));
        $c->setSignatureService($container->get(SignatureService::class));
        $this->init($c, $container);
        return $c;
    }
}