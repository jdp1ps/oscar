<?php

namespace UnicaenAppTest\Controller\Plugin;

/**
 * Description of LdapGroupServiceFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MailFactoryTest extends BaseServiceFactoryTest
{
    protected $factoryClass = 'UnicaenApp\Controller\Plugin\MailFactory';
    protected $serviceClass = 'UnicaenApp\Controller\Plugin\Mail';
    protected $mailOptions  = array(
        'transport_options' => array(
            'host' => 'smtp.unicaen.fr',
            'port' => 25,
        ),
    );
    protected $moduleOptions;
    
    protected function setUp()
    {
        parent::setUp();
        
        $this->moduleOptions = $this->getMock('UnicaenApp\Options\ModuleOptions', array('getMail'));
        
        $this->serviceManager->expects($this->once())
                ->method('get')
                ->with('unicaen-app_module_options')
                ->will($this->returnValue($this->moduleOptions));
        
        $this->pluginManager->expects($this->once())
                ->method('getServiceLocator')
                ->will($this->returnValue($this->serviceManager));
       
    }
    
    public function testCanCreateServiceWithDefaultOptions()
    {
        $this->moduleOptions->expects($this->once())
                ->method('getMail')
                ->will($this->returnValue($this->mailOptions));
        
        $service = $this->factory->createService($this->pluginManager);
        
        $this->assertInstanceOf($this->serviceClass, $service);
        $this->assertEquals(array(), $service->getRedirectTo());
        $this->assertFalse($service->getDoNotSend());
    }

    public function provideValidOptions()
    {
        return array(
            'empty' => array(
                array(), 
                array(), 
                false),
            'redirect_to' => array(
                array('redirect_to' => array('email@domain.fr')), 
                array('email@domain.fr'), 
                false),
            'do_not_send' => array(
                array('do_not_send' => true), 
                array(), 
                true),
            'redirect_to-do_not_send' => array(
                array('redirect_to' => array('email@domain.fr'), 'do_not_send' => true), 
                array('email@domain.fr'), 
                true),
        );
    }
    
    /**
     * @dataProvider provideValidOptions
     * @param type $options
     * @param type $expectedRedirectTo
     * @param type $expectedDoNotSend
     */
    public function testCanCreateServiceWithValidOptions($options, $expectedRedirectTo, $expectedDoNotSend)
    {
        $this->mailOptions = array_merge($this->mailOptions, $options);
     
        $this->moduleOptions->expects($this->once())
                ->method('getMail')
                ->will($this->returnValue($this->mailOptions));
        
        $service = $this->factory->createService($this->pluginManager);
        
        $this->assertInstanceOf($this->serviceClass, $service);
        $this->assertEquals($expectedRedirectTo, $service->getRedirectTo());
        $this->assertEquals($expectedDoNotSend, $service->getDoNotSend());
    }
    
    /**
     * @expectedException \Zend\ServiceManager\Exception\InvalidArgumentException
     */
    public function testCanCreateServiceWithInvalidOptions()
    {
        $this->mailOptions = array();
     
        $this->moduleOptions->expects($this->once())
                ->method('getMail')
                ->will($this->returnValue($this->mailOptions));
        
        $service = $this->factory->createService($this->pluginManager);
    }
}