<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Oscar;

use Laminas\EventManager\Event;
use Laminas\Ldap\Ldap;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceManager;
use Oscar\Service\ContractDocumentService;
use Oscar\Service\LoggerService;
use UnicaenAuthentification\Event\UserAuthenticatedEvent;

class Module
{

    private $logger;

    private $sm;

    public function onBootstrap(MvcEvent $e)
    {
        $this->sm = $e->getApplication()->getServiceManager();
    }

    /**
     * Lors de la connexion, on enregistre la Datetime de login
     * @param $e UserAuthenticatedEvent
     */
    public function onUserLoginFail($e)
    {
        $this->sm->get('Logger')->info("LDAP auth fail : " . $e->getParam('messages', 'No message'));
    }

    /**
     * Lors de la connexion, on enregistre la Datetime de login
     * @param $e Event
     */
    public function onSignatureChange($e)
    {
        $this->sm->get(ContractDocumentService::class)->onSignatureChange($e);
    }

    // FIX : ZendFramework 3
    public function init(ModuleManager $manager)
    {
        $manager->getEventManager()->getSharedManager()->attach('*', 'signature:status', function ($e) {
            $this->onSignatureChange($e);
        });
        $manager->getEventManager()->getSharedManager()->attach('*', 'authentification.ldap.fail', function ($e) {
            $this->onUserLoginFail($e);
        });
// EVENT TRACKER
//        $manager->getEventManager()->getSharedManager()->attach('*', '*', function ($e) {
//            echo $e->getName()."\n";
//            $w = fopen('/tmp/debug-oscar.txt', 'a+');
//            fwrite($w, $e->getName()."\n");
//        });
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
