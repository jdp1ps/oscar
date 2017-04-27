<?php

namespace UnicaenAppTest\Options;

use UnicaenAppTest\BaseServiceFactoryTest;

/**
 * Description of ModuleOptionsFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ModuleOptionsFactoryTest extends BaseServiceFactoryTest
{
    protected $factoryClass = 'UnicaenApp\Options\ModuleOptionsFactory';
    protected $serviceClass = 'UnicaenApp\Options\ModuleOptions';
    
    public function testCanCreateService()
    {
        $config = new \Zend\Config\Config(array(
            'unicaen-app' => array(),
        ));
        $this->serviceManager->expects($this->once())
                ->method('get')
                ->with('Configuration')
                ->will($this->returnValue($config));
        
        $service = $this->factory->createService($this->serviceManager);
        
        $this->assertInstanceOf($this->serviceClass, $service);
    }
}