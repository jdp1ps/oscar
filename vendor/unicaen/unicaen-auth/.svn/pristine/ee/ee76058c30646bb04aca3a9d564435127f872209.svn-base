<?php
namespace UnicaenAuthTest\Authentication\Storage;

use PDOException;
use PHPUnit_Framework_TestCase;
use UnicaenAuth\Authentication\Storage\Db;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceManager;
use ZfcUser\Entity\User;
use UnicaenAuth\Authentication\Storage\ChainEvent;

/**
 * Description of DbTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class DbTest extends PHPUnit_Framework_TestCase
{
    protected $storage;
    protected $serviceManager;
    protected $mapper;
    protected $event;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager', ['get']);
        $this->innerStorage   = $this->getMock('Zend\Authentication\Storage\Session', ['write', 'clear', 'read']);
        $this->mapper         = $this->getMock('ZfcUser\Mapper\User', ['findById', 'findByUsername']);
        $this->event          = new ChainEvent();
        $this->storage        = new Db();

        $this->storage->setMapper($this->mapper)
                      ->setServiceManager($this->serviceManager)
                      ->setStorage($this->innerStorage);
    }

    public function testCanRetrieveDefaultInnerStorage()
    {
        $storage = new Db();
        $this->assertInstanceOf('Zend\Authentication\Storage\StorageInterface', $storage->getStorage());
    }

    public function testCanRetrieveMapperFromServiceManager()
    {
        $this->serviceManager->expects($this->once())
                ->method('get')
                ->with('zfcuser_user_mapper')
                ->will($this->returnValue('mapper'));

        $this->storage->setMapper(null);
        $this->assertEquals('mapper', $this->storage->getMapper());
    }

    public function testCanWrite()
    {
        $this->event->setParam('contents', 12);

        $this->innerStorage->expects($this->once())
                     ->method('write')
                     ->with(12);

        $this->storage->write($this->event);
    }

    public function getException()
    {
        return [
            [new PDOException()],
            [new ServiceNotFoundException()],
        ];
    }

    /**
     * @dataProvider getException
     */
    public function testReadingReturnsNullIfFindByIdThrowsException($exception)
    {
        $this->innerStorage->expects($this->once())
                     ->method('read')
                     ->will($this->returnValue(12));
        $this->mapper->expects($this->once())
                     ->method('findById')
                     ->will($this->throwException($exception));

        $this->storage->read($this->event);
        $this->assertEquals(['db' => null], $this->event->getContents());
    }

    public function testReadingReturnsEntityIfUserIsFoundById()
    {
        $entity = new User();

        $this->innerStorage->expects($this->once())
                     ->method('read')
                     ->will($this->returnValue(12));
        $this->mapper->expects($this->once())
                     ->method('findById')
                     ->will($this->returnValue($entity));

        $this->storage->read($this->event);
        $this->assertEquals(['db' => $entity], $this->event->getContents());
    }

    /**
     * @dataProvider getException
     */
    public function testReadingReturnsNullIfFindByUsernameThrowsException($exception)
    {
        $this->innerStorage->expects($this->once())
                     ->method('read')
                     ->will($this->returnValue('username'));
        $this->mapper->expects($this->once())
                     ->method('findById')
                     ->will($this->returnValue(null));
        $this->mapper->expects($this->once())
                     ->method('findByUsername')
                     ->will($this->throwException($exception));

        $this->storage->read($this->event);
        $this->assertEquals(['db' => null], $this->event->getContents());
    }

    public function testReadingReturnsEntityIfUserFoundByUsername()
    {
        $entity = new User();

        $this->innerStorage->expects($this->once())
                     ->method('read')
                     ->will($this->returnValue('username'));
        $this->mapper->expects($this->once())
                     ->method('findById')
                     ->will($this->returnValue(null));
        $this->mapper->expects($this->once())
                     ->method('findByUsername')
                     ->will($this->returnValue($entity));

        $this->storage->read($this->event);
        $this->assertEquals(['db' => $entity], $this->event->getContents());
    }

    public function testReadingReturnsEntityInInnerStorage()
    {
        $entity = new User();

        $this->innerStorage->expects($this->once())
                     ->method('read')
                     ->will($this->returnValue($entity));
        $this->mapper->expects($this->never())
                     ->method('findById');
        $this->mapper->expects($this->never())
                     ->method('findByUsername');

        $this->storage->read($this->event);
        $this->assertEquals(['db' => $entity], $this->event->getContents());
    }

    public function testCanClear()
    {
        $this->innerStorage->expects($this->once())
                     ->method('clear');

        $this->storage->clear($this->event);
    }
}