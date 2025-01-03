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
use Oscar\Service\ProjectGrantService;

class ApiControllerFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new ApiController();
        $this->init($c, $container);
        $c->setActivityService($container->get(ProjectGrantService::class));
        return $c;
    }
}