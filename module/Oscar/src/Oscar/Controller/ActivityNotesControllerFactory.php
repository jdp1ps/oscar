<?php

namespace Oscar\Controller;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Oscar\Service\OscarUserContext;

class ActivityNotesControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new ActivityNotesController();
        $c->setEntityManager($container->get(EntityManager::class));
        $c->setOscarUserContextService($container->get(OscarUserContext::class));
        return $c;
    }
}
