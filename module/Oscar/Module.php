<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Oscar;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Monolog\Logger;
use Oscar\Auth\UserAuthenticatedEventListener;
use Oscar\Entity\LogActivity;
use Oscar\Entity\ActivityLogRepository;
use Oscar\Entity\Authentification;
use Oscar\Exception\OscarException;
use Oscar\Service\ActivityLogService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use UnicaenAuth\Authentication\Adapter\Ldap;
use UnicaenAuth\Event\UserAuthenticatedEvent;
use UnicaenAuth\Provider\Identity\ChainEvent;
use UnicaenAuth\Service\User;
use UnicaenAuth\Service\UserContext;
use Zend\Authentication\Result;
use Zend\Console\Adapter\AdapterInterface;
use Zend\EventManager\Event;
use Zend\Http\PhpEnvironment\Request;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\ServiceManager\ServiceManager;
use ZfcUser\Authentication\Adapter\AdapterChainEvent;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
