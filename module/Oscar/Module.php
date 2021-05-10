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
use Zend\Authentication\Result as AuthenticationResult;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use ZfcUser\Authentication\Adapter\AdapterChainEvent;

class Module
{


    public function onBootstrap(MvcEvent $e)
    {
        // TODO a tester
        $e->getApplication()->getEventManager()->getSharedManager()->attach(
            "*",
            'authenticate', //"authentication.success",
                    [$this, "onUserLogin"],
                100
        );
    }

    public function onUserLogin( $e ) {
        die("onUserLogin");

//        if (is_string(\$identity = \$e->getIdentity())) {
//            // login de l'utilisateur authentifié
//            \$username = \$identity;
//            //...
//        } else {
//            // id de l'utilisateur authentifié dans la table
//            \$id = \$identity;
//            //...
//        }
      //...
}

    // FIX : ZendFramework 3
    public function init(ModuleManager $manager)
    {

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
