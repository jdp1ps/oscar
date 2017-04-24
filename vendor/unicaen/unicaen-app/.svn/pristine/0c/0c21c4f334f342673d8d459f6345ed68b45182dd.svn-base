<?php

namespace UnicaenAppTest\View\Helper;

/**
 * Description of AppLinkFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AppLinkFactoryTest extends BaseServiceFactoryTest
{
    protected $factoryClass = 'UnicaenApp\View\Helper\AppLinkFactory';
    protected $serviceClass = 'UnicaenApp\View\Helper\AppLink';
    
    public function provideRouteMatch()
    {
        return array(
            array(null),
            array($this->getMock('Zend\Mvc\Router\RouteMatch', array(), array(), '', false)),
        );
    }
    
    /**
     * @dataProvider provideRouteMatch
     * @param type $routeMatch
     */
    public function testCanCreateService($routeMatch)
    {
        $event = $this->getMock('Zend\Mvc\MvcEvent', array('getRouteMatch'));
        $event->expects($this->once())
                ->method('getRouteMatch')
                ->will($this->returnValue($routeMatch));
        
        $application = $this->getMock('Zend\Mvc\Application', array('getMvcEvent'), array(), '', false);
        $application->expects($this->once())
                ->method('getMvcEvent')
                ->will($this->returnValue($event));
        
        $router = $this->getMock('Zend\Mvc\Router\SimpleRouteStack', array());
        $map = array(
            array('application', $application),
            array('HttpRouter',  $router),
        );
        $this->serviceManager->expects($this->any())
                ->method('get')
                ->will($this->returnValueMap($map));
        
        $service = $this->factory->createService($this->pluginManager);
        
        $this->assertInstanceOf($this->serviceClass, $service);
        $this->assertEquals($routeMatch, $service->getRouteMatch());
    }
}