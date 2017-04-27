<?php

namespace UnicaenAuthTest\Authentication\Storage;

use UnicaenAuth\Authentication\Storage\Chain;

/**
 * Description of ChainTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ChainTest extends \PHPUnit_Framework_TestCase
{
    protected $storage;
    protected $innerStorage;
    protected $eventManager;
    protected $event;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->storage      = new Chain();
        $this->innerStorage = $this->getMock('Zend\Authentication\Storage\Session', ['isEmpty', 'read', 'write', 'clear']);
        $this->eventManager = $this->getMock('Zend\EventManager\EventManager', ['trigger']);
        $this->event        = $this->getMock('UnicaenAuth\Authentication\Storage\ChainEvent', ['getContents']);

        $this->storage->setStorage($this->innerStorage)
                ->setEventManager($this->eventManager)
                ->setEvent($this->event);
    }

    public function testCanRetrieveDefaultInnerStorage()
    {
        $storage = new Chain();
        $this->assertInstanceOf('Zend\Authentication\Storage\StorageInterface', $storage->getStorage());
    }

    public function testCanSetInnerStorage()
    {
        $storage = new Chain();
        $storage->setStorage($inner = new \Zend\Authentication\Storage\Session());
        $this->assertSame($inner, $storage->getStorage());
    }

    public function testCanRetrieveDefaultEventManager()
    {
        $storage = new Chain();
        $this->assertInstanceOf('Zend\EventManager\EventManagerInterface', $storage->getEventManager());
    }

    public function testCanSetEventManager()
    {
        $storage = new Chain();
        $storage->setEventManager($em = new \Zend\EventManager\EventManager());
        $this->assertSame($em, $storage->getEventManager());
    }

    public function testCanRetrieveDefaultEvent()
    {
        $storage = new Chain();
        $this->assertInstanceOf('UnicaenAuth\Authentication\Storage\ChainEvent', $storage->getEvent());
    }

    public function testCanSetEvent()
    {
        $storage = new Chain();
        $storage->setEvent($e = new \UnicaenAuth\Authentication\Storage\ChainEvent());
        $this->assertSame($e, $storage->getEvent());
    }

    public function testCanGetEmptiness()
    {
        $this->eventManager->expects($this->never())
                ->method('trigger');
        $this->innerStorage->expects($this->once())
                ->method('isEmpty')
                ->will($this->returnValue('result'));
        $this->assertEquals('result', $this->storage->isEmpty());
    }

    public function testCanWrite()
    {
        $this->innerStorage->expects($this->once())
                ->method('write')
                ->with(12);
        $this->eventManager->expects($this->once())
                ->method('trigger')
                ->with('write', $this->logicalAnd(
                        $this->isInstanceOf('UnicaenAuth\Authentication\Storage\ChainEvent'),
                        $this->attributeEqualTo('params', ['contents' => 12])));
        $this->storage->write(12);
    }

    public function testReadingReturnsCollectedIdentities()
    {
        $this->eventManager->expects($this->once())
                ->method('trigger')
                ->with('read', $this->isInstanceOf('UnicaenAuth\Authentication\Storage\ChainEvent'));
        $this->event->expects($this->once())
                ->method('getContents')
                ->will($this->returnValue($expected = ['db' => 'db identity', 'ldap' => 'ldap identity']));
        $this->assertEquals($expected, $this->storage->read());
    }

    /**
     * @depends testReadingReturnsCollectedIdentities
     */
    public function testReadingDoesNotCollectIdentitiesTwice()
    {
        $this->eventManager->expects($this->once())
                ->method('trigger')
                ->with('read', $this->isInstanceOf('UnicaenAuth\Authentication\Storage\ChainEvent'));
        $this->event->expects($this->once())
                ->method('getContents')
                ->will($this->returnValue($expected = ['db' => 'db identity', 'ldap' => 'ldap identity']));
        $this->assertEquals($expected, $this->storage->read());

        $this->eventManager->expects($this->never())
                ->method('trigger');
        $this->event->expects($this->never())
                ->method('getContents');
        $this->assertEquals($expected, $this->storage->read());
    }

    public function getEmptyEventContents()
    {
        return [
            [[]],
            [null],
        ];
    }

    /**
     * @dataProvider getEmptyEventContents
     */
    public function testReadingReturnsNullIfNoIdentitiesCollected($contents)
    {
        $this->eventManager->expects($this->once())
                ->method('trigger')
                ->with('read', $this->isInstanceOf('UnicaenAuth\Authentication\Storage\ChainEvent'));
        $this->event->expects($this->once())
                ->method('getContents')
                ->will($this->returnValue($contents));
        $this->assertNull($this->storage->read());
    }

    /**
     * @depends testReadingReturnsCollectedIdentities
     */
    public function testCanClear()
    {
        $this->innerStorage->expects($this->once())
                ->method('clear');
        $this->eventManager->expects($this->once())
                ->method('trigger')
                ->with('clear', $this->isInstanceOf('UnicaenAuth\Authentication\Storage\ChainEvent'));
        $this->storage->clear();
    }
}