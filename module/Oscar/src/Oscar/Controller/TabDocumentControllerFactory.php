<?php
/**
 * Created by PhpStorm.
 * User: marie
 * Date: 17/10/22
 * Time: 14:50
 */

namespace Oscar\Controller;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Oscar\Factory\AbstractOscarFactory;
use Oscar\Service\NotificationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class TabDocumentControllerFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new TabDocumentController();
        $this->init($c, $container);
        return $c;
    }
}
