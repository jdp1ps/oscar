<?php

namespace UnicaenAppTest\View\Helper;

use Zend\Config\Config;

/**
 * Description of AppInfosFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AppInfosFactoryTest extends BaseServiceFactoryTest
{
    protected $factoryClass = 'UnicaenApp\View\Helper\AppInfosFactory';
    protected $serviceClass = 'UnicaenApp\View\Helper\AppInfos';
    
    public function testCanCreateService()
    {
        $moduleOptions = $this->getMock('UnicaenApp\Options\ModuleOptions', array('getAppInfos'));
        $moduleOptions->expects($this->once())
                ->method('getAppInfos')
                ->will($this->returnValue($infos = array('nom' => "Mon application")));
        
        $this->serviceManager->expects($this->once())
                ->method('get')
                ->with('unicaen-app_module_options')
                ->will($this->returnValue($moduleOptions));
       
        $service = $this->factory->createService($this->pluginManager);
        
        $this->assertInstanceOf($this->serviceClass, $service);
        $this->assertEquals(new Config($infos), $service->getConfig());
    }
}