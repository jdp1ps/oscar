<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 22/07/15
 * Time: 14:06
 */

namespace UnicaenAppTest\Message;

use UnicaenApp\Message\MessageServiceFactory;

class MessageServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $serviceLocator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageRepository;

    protected function setUp()
    {
        $this->serviceLocator = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface');

        $this->givenThatServiceLocatorWillReturnMessageRepository();
    }

    public function testCanCreateService()
    {
        $factory = new MessageServiceFactory();
        $service = $factory->createService($this->serviceLocator);
        $this->assertInstanceOf('UnicaenApp\Message\MessageService', $service);
    }

    private function givenThatServiceLocatorWillReturnMessageRepository()
    {
        $this->messageRepository = $this->getMockBuilder('UnicaenApp\Message\MessageRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceLocator
            ->method('get')
            ->with('MessageRepository')
            ->willReturn($this->messageRepository);

        return $this;
    }
}
