<?php
namespace UnicaenAppTest\View\Helper\Navigation;

use PHPUnit_Framework_TestCase;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Renderer\PhpRenderer;

/**
 * Description of AbstractTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
abstract class AbstractTest extends PHPUnit_Framework_TestCase
{
    protected $files;
    protected $navigation;
    protected $routes;
    protected $helperClass;
    protected $helper;
    protected $container;
    protected $serviceManager;
    protected $routeMatch;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->files = __DIR__ . '/_files';
        
        $nav    = require $this->files . '/navigation/' . $this->navigation;
        $routes = require $this->files . '/routes/'     . $this->routes;

        // setup service manager
        $smConfig = array(
            'modules'                 => array(),
            'module_listener_options' => array(
                'config_cache_enabled' => false,
                'cache_dir'            => 'data/cache',
                'module_paths'         => array(),
                'extra_config'         => array(
                    'service_manager' => array(
                        'factories' => array(
                            'Config' => function () use ($nav) {
                                return array(
                                    'navigation' => array(
                                        'default' => $nav,
                                    ),
                                );
                            }
                        ),
                    ),
                ),
            ),
        );
        $sm = $this->serviceManager = new ServiceManager(new ServiceManagerConfig);
        $sm->setService('ApplicationConfig', $smConfig);
        $sm->get('ModuleManager')->loadModules();
        $sm->get('Application')->bootstrap();
        $sm->setFactory('Navigation', 'Zend\Navigation\Service\DefaultNavigationFactory');

        $router = new TreeRouteStack();
        $router->addRoutes($routes);

        $this->routeMatch = new RouteMatch(array(/* 'controller' => 'contact', 'action' => 'ajouter' */));

        $app = $this->serviceManager->get('Application');
        $app->getMvcEvent()->setRouter($router)
                ->setRouteMatch($this->routeMatch);

        // récupération de la navigation via la factory DefaultNavigationFactory du service manager
        // (nécessaire pour injecter le router et le route match dans chaque page Mvc)
        $this->container = $this->serviceManager->get('Navigation');

        $this->helper = new $this->helperClass();
        $this->helper->setView(new PhpRenderer())
                ->setContainer($this->container)
                ->setServiceLocator($this->serviceManager);
    }

    /**
     * Returns the content of the expected $file
     * 
     * @param string $file
     * @return string
     */
    protected function getExpected($file)
    {
        return file_get_contents($this->files . '/expected/' . $file);
    }
}