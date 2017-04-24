<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 22/07/15
 * Time: 13:41
 */

namespace UnicaenAppTest\Message;


use UnicaenApp\Message\MessageFormatter;

class MessageFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $message;

    protected function setUp()
    {
        $this->message = $this->getMockBuilder('UnicaenApp\Message\Message')
            ->disableOriginalConstructor()
            ->setMethods(['getTextForContext', 'getSatisfiedSpecificationSentBackData'])
            ->getMock();
    }

    public function testThatSpecifiedParametersAreMergeWithSatisfiedSpecificationSentBackData()
    {
        $this->message
            ->expects($this->once())
            ->method('getTextForContext')
            ->willReturn("My name is {name} {lastName}");

        $this->message
            ->expects($this->once())
            ->method('getSatisfiedSpecificationSentBackData')
            ->willReturn(['lastName' => 'Joe']);

        $this->assertEquals("My name is Bobby Joe", MessageFormatter::format($this->message, ['name' => 'Bobby']));
    }
}
