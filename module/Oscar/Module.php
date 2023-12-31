<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Oscar;

use Oscar\Auth\UserAuthenticatedEventListener;
use Oscar\Entity\Authentification;
use UnicaenAuth\Authentication\Adapter\Ldap;
use UnicaenAuth\Event\UserAuthenticatedEvent;
use Zend\Authentication\Result;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\EventManager\Event;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use ZfcUser\Authentication\Adapter\AdapterChainEvent;

class Module
{

    private $logger;

    public function onBootstrap(MvcEvent $e)
    {
        // FIX : Login
        $e->getApplication()->getEventManager()->getSharedManager()->attach(
            "*",
            'prePersist', //"authentication.success",
            [$this, "onUserLogin"],
            100
        );

        $e->getApplication()->getEventManager()->getSharedManager()->attach(
            "*",
            Ldap::LDAP_AUTHENTIFICATION_FAIL, //"authentication.success",
            [$this, "onLdapError"],
            100
        );

        $this->logger = $e->getApplication()->getServiceManager()->get('Logger');

    }

    /**
     * Lors de la connexion, on enregistre la Datetime de login
     * @param $e UserAuthenticatedEvent
     */
    public function onUserLogin($e)
    {
        if (get_class($e) == UserAuthenticatedEvent::class) {
            /** @var Authentification $user */
            $user = $e->getDbUser();
            $user->setDateLogin(new \DateTime());
        }
    }

    // FIX : ZendFramework 3
    public function init(ModuleManager $manager)
    {
        $sharedEventManager = $manager->getEventManager()->getSharedManager();
    }

    public function onLdapError(Event $event)
    {
        if ($event->getName() == Ldap::LDAP_AUTHENTIFICATION_FAIL) {
            $userMessage = "Problème d'authentification LDAP";
            $log = "Problème d'authentification LDAP";

            /** @var Result $messages */
            $messages = $event->getParam('result');
            if( $messages != null && get_class($messages) == Result::class ){
                $log .= " : \n";
                foreach ($messages->getMessages() as $message) {
                    $log .= $message . "\n";
                }
            }
            $this->logger->error($log);
            error_log($log);
        }
    }

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
