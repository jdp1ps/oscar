<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 22/07/15
 * Time: 10:58
 */

namespace UnicaenAppTest\Message;


use UnicaenApp\Message\MessageRepository;

class MessageRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCanConstructWithEmptyConfig()
    {
        new MessageRepository([]);
    }

    /**
     * @expectedException \UnicaenApp\Message\Exception\MessageNotFoundException
     * @expectedExceptionMessage Message introuvable avec l'id
     */
    public function testRetrievingMessageByUnknownIdThrowsException()
    {
        $messages = [
            $this->newMessageMock('ID_1'),
            $this->newMessageMock('ID_2'),
        ];

        $repo = new MessageRepository($messages);
        $repo->messageById('ID_unknown');
    }

    public function testCanRetrieveMessageById()
    {
        $messages = [
            $this->newMessageMock('ID_1'),
            $this->newMessageMock('ID_2'),
        ];

        $repo = new MessageRepository($messages);
        $message = $repo->messageById('ID_2');

        $this->assertInstanceOf('UnicaenApp\Message\Message', $message);
        $this->assertEquals('ID_2', $message->getId());
    }

    private function newMessageMock($id)
    {
        $message = $this->getMockBuilder('UnicaenApp\Message\Message')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $message
            ->method('getId')
            ->willReturn($id);

        return $message;
    }
}
