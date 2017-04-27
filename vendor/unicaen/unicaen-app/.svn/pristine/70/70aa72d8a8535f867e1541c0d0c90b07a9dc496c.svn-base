<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 22/07/15
 * Time: 14:36
 */

namespace UnicaenAppTest\Message;

use UnicaenApp\Message\MessageService;

class MessageServiceTest extends \PHPUnit_Framework_TestCase
{
    private $messageRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $message;

    /**
     * @var MessageService
     */
    private $messageService;

    protected function setUp()
    {
        $this->message = $this->getMockBuilder('UnicaenApp\Message\Message')
            ->disableOriginalConstructor()
            ->setMethods(['applyContext', 'getTextForContext'])
            ->getMock();

        $this->messageRepository = $this->getMockBuilder('UnicaenApp\Message\MessageRepository')
            ->disableOriginalConstructor()
            ->setMethods(['messageById'])
            ->getMock();
        $this->messageRepository
            ->method('messageById')
            ->willReturn($this->message);

        $this->messageService = new MessageService($this->messageRepository);
    }

    public function testSpecifyingGlobalContext()
    {
        $context = ['role' => 'admin'];

        $this->message
            ->expects($this->once())
            ->method('applyContext')
            ->with($context)
            ->willReturnSelf();

        $this->messageService
            ->setContext($context) // sets global context
            ->render('MESSAGE_ID');
    }

    public function testSpecifyingSubstitutionContextIsUsedInPlaceOfGlobalContext()
    {
        $initialContext = ['role' => 'admin'];
        $anotherContext = ['role' => 'guest'];

        $this->message
            ->expects($this->once())
            ->method('applyContext')
            ->with($anotherContext)
            ->willReturnSelf();

        $this->messageService
            ->setContext($initialContext)
            ->render('MESSAGE_ID', [], $anotherContext);
    }

    public function testSpecifyingSubstitutionContextDoesNotOverwriteGlobalContext()
    {
        $initialContext = ['role' => 'admin'];
        $anotherContext = ['role' => 'guest'];

        $this->message
            ->expects($this->once())
            ->method('applyContext')
            ->with($anotherContext)
            ->willReturnSelf();

        $this->messageService
            ->setContext($initialContext) // sets global context
            ->render('MESSAGE_ID', [], $anotherContext); // but renders with another context

        $this->assertEquals($initialContext, $this->messageService->getContext());
    }


}
