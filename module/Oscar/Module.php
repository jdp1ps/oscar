<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Oscar;

use Laminas\Ldap\Ldap;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\MvcEvent;
use UnicaenAuthentification\Event\UserAuthenticatedEvent;

class Module
{

    private $logger;

    public function onBootstrap(MvcEvent $e)
    {

    }

    /**
     * Lors de la connexion, on enregistre la Datetime de login
     * @param $e UserAuthenticatedEvent
     */
    public function onUserLogin($e)
    {

    }

    // FIX : ZendFramework 3
    public function init(ModuleManager $manager)
    {

    }

    public function onLdapError(Event $event)
    {

    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
