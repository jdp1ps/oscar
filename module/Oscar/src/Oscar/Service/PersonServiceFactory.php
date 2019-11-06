<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 19/09/19
 * Time: 18:45
 */

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Oscar\Factory\AbstractOscarFactory;
use Oscar\Traits\UseNotificationService;
use Oscar\Traits\UseOscarUserContextService;
use Zend\ServiceManager\Factory\FactoryInterface;

class PersonServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new PersonService();
      //  $s->seServiceContainer($container);
        $s->setActivityLogService($container->get(ActivityLogService::class));
        $s->setOscarUserContextService($container->get(OscarUserContext::class));
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $s->setEntityManager($container->get(EntityManager::class));
        $s->setLoggerService($container->get('Logger'));
//        //$s = $this->init($s, $container);

        return $s;
    }
}