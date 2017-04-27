<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 22/07/15
 * Time: 16:17
 */

namespace UnicaenAppTest\Message\View\Helper;

use UnicaenApp\Message\View\Helper\MessageHelper;

class MessageHelperTest extends \PHPUnit_Framework_TestCase
{
    private $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageService;

    protected function setUp()
    {
        $this->messageService = $this->getMockBuilder('UnicaenApp\Message\MessageService')
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $this->helper = new MessageHelper($this->messageService);
    }

    public function testInvokingHelperReturnsSelf()
    {
        $this->assertSame($this->helper, $this->helper->__invoke());
    }

    public function testRenderingIsDelegatedToMessageService()
    {
        $messageId  = 'ID';
        $parameters = [];
        $context    = ['role' => 'admin'];

        $this->messageService
            ->expects($this->once())
            ->method('render')
            ->with($messageId, $parameters, $context);

        $this->helper->render($messageId, $parameters, $context);
    }
}
