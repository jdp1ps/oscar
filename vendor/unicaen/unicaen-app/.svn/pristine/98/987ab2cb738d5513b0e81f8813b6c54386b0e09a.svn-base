<?php

namespace UnicaenAppTest\Controller\Plugin;

/**
 * Description of BaseLdapServiceFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
abstract class BaseLdapServiceFactoryTest extends BaseServiceFactoryTest
{
    protected $ldapServiceName;
    protected $ldapServiceClass;
    
    public function testCanCreateService()
    {
        $this->serviceManager->expects($this->once())
                ->method('get')
                ->with($this->ldapServiceName)
                ->will($this->returnValue($this->getMock($this->ldapServiceClass, array())));
        
        $this->pluginManager->expects($this->once())
                ->method('getServiceLocator')
                ->will($this->returnValue($this->serviceManager));
       
        $this->assertInstanceOf($this->serviceClass, $this->factory->createService($this->pluginManager));
    }
}