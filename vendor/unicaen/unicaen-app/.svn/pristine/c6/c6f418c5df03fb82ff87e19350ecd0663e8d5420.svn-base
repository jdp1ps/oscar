<?php

namespace UnicaenAppTest\View\Helper;

/**
 * Description of BaseLdapServiceFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
abstract class BaseServiceFactoryTest extends \UnicaenAppTest\BaseServiceFactoryTest
{
    protected $pluginManager;
    
    protected function setUp()
    {
        parent::setUp();
        
        $this->pluginManager = $this->getMock('Zend\View\HelperPluginManager', array('getServiceLocator'));
        
        $this->pluginManager->expects($this->once())
                ->method('getServiceLocator')
                ->will($this->returnValue($this->serviceManager));
    }
}