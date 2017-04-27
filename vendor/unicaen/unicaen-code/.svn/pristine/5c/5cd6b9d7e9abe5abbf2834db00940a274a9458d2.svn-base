<?php

namespace UnicaenCode;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;

include_once 'Functions.php';

/**
 *
 *
 * @author Laurent LECLUSE <laurent.lecluse at unicaen.fr>
 */
class Module implements ConfigProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        // on transmet pour qu'il soit accessible de partout en cas de besoins
        // Oui c'est crade mais j'assume!!
        $sm = $e->getApplication()->getServiceManager();
        Util::setServiceLocator($sm);
    }



    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}