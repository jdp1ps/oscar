<?php

namespace UnicaenAppTest\Session;

use UnicaenAppTest\BaseServiceFactoryTest;
use Zend\Session\Container;

/**
 * Description of ModuleOptionsFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ModuleOptionsFactoryTest extends BaseServiceFactoryTest
{
    protected $factoryClass = 'UnicaenApp\Session\SessionManagerFactory';
    protected $serviceClass = 'Zend\Session\SessionManager';
    
    public function testCanCreateService()
    {
        $appInfos = array(
            'nom' => "Mon application",
        );
        
        $moduleOptions = $this->getMock('UnicaenApp\Options\ModuleOptions', array('getAppInfos'));
        $moduleOptions->expects($this->once())
                ->method('getAppInfos')
                ->will($this->returnValue($appInfos));
        
        $this->serviceManager->expects($this->once())
                ->method('get')
                ->with('unicaen-app_module_options')
                ->will($this->returnValue($moduleOptions));
        
        $service = $this->factory->createService($this->serviceManager); /* @var $service \Zend\Session\SessionManager */

        $this->assertInstanceOf($this->serviceClass, $service);
        $this->assertSame($service, Container::getDefaultManager());
        $this->assertNotEmpty($service->getValidatorChain()->getListeners('session.validate'));
        $this->assertEquals($service->getConfig()->getName(), md5($appInfos['nom']));
    }
}