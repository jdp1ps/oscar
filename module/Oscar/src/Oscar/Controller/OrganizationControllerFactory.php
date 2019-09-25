<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 25/09/19
 * Time: 09:06
 */

namespace Oscar\Controller;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Oscar\Service\OrganizationService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class OrganizationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new OrganizationController();
        $c->setOrganizationService($container->get(OrganizationService::class));
        return $c;
    }
}