<?php
namespace UnicaenAuth\Authentication\Adapter;

use UnicaenApp\Exception;
use UnicaenAuth\Authentication\Adapter\Cas;
use UnicaenAuth\Authentication\Adapter\Db;
use UnicaenAuth\Authentication\Adapter\Ldap;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of AbstractFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AbstractFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return strpos($requestedName, __NAMESPACE__) === 0 && class_exists($requestedName);
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        switch ($requestedName) {
            case __NAMESPACE__ . '\Ldap':
                $adapter = new Ldap();
                break;
            case __NAMESPACE__ . '\Db':
                $adapter = new Db();
                break;
            case __NAMESPACE__ . '\Cas':
                $adapter = new Cas();
                break;
            default:
                throw new Exception("Service demandé inattendu : '$requestedName'!");
                break;
        }

        if ($adapter instanceof \Zend\EventManager\EventManagerAwareInterface) {
            $eventManager = $serviceLocator->get('event_manager');
            $adapter->setEventManager($eventManager);
            $userService = $serviceLocator->get('unicaen-auth_user_service'); /* @var $userService \UnicaenAuth\Service\User */
            $eventManager->attach('userAuthenticated', [$userService, 'userAuthenticated'], 100);
        }

        return $adapter;
    }
}