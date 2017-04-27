<?php

namespace UnicaenApp\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Console\Console;
use Zend\Mvc\Router\RouteMatch;

/**
 * Description of AppLinkFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AppLinkFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $helperPluginManager
     * @return AppInfos
     */
    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        $sl     = $helperPluginManager->getServiceLocator();
        $router = Console::isConsole() ? 'HttpRouter' : 'Router';
        $match  = $sl->get('application')->getMvcEvent()->getRouteMatch();
        $helper = new AppLink();

        $helper->setRouter($sl->get($router));
        
        if ($match instanceof RouteMatch) {
            $helper->setRouteMatch($match);
        }
        
        return $helper;
    }
}