<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 22/07/15
 * Time: 15:38
 */

namespace UnicaenAppTest\Message\View\Helper;

use UnicaenApp\Message\View\Helper\MessageHelperFactory;

class MessageHelperFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $helperPluginManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceLocator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageService;

    protected function setUp()
    {
        $this->messageService = $this->getMockBuilder('UnicaenApp\Message\MessageService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceLocator      = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface');
        $this->helperPluginManager = $this->getMockForHelperPluginManager($this->serviceLocator);
    }

    private function getMockForHelperPluginManager($serviceLocator)
    {
        $helperPluginManager = $this->getMockBuilder('Zend\View\HelperPluginManager')
            ->disableOriginalConstructor()
            ->setMethods(['getServiceLocator'])
            ->getMock();
        $helperPluginManager
            ->method('getServiceLocator')
            ->willReturn($serviceLocator);

        return $helperPluginManager;
    }

    public function testCreatingServiceAsksServiceLocatorForMessageService()
    {
        $this->serviceLocator
            ->expects($this->once())
            ->method('get')
            ->with('MessageService')
            ->willReturn($this->messageService);

        $factory = new MessageHelperFactory();
        $factory->createService($this->helperPluginManager);
    }

    public function testCreatingServiceReturnsMessageHelper()
    {
        $this->serviceLocator
            ->method('get')
            ->with('MessageService')
            ->willReturn($this->messageService);

        $factory = new MessageHelperFactory();
        $helper = $factory->createService($this->helperPluginManager);
        $this->assertInstanceOf('UnicaenApp\Message\View\Helper\MessageHelper', $helper);
    }
}
