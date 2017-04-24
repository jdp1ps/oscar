<?php
namespace UnicaenAuthTest\Authentication\Storage;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Entity\Ldap\People;
use UnicaenAuth\Entity\Ldap\People as AuthPeople;
use UnicaenAppTest\Entity\Ldap\TestAsset\People as PeopleTestAsset;
use UnicaenAuth\Authentication\Storage\Ldap;
use UnicaenAuth\Authentication\Storage\ChainEvent;

/**
 * Description of LdapTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class LdapTest extends PHPUnit_Framework_TestCase
{
    protected $storage;
    protected $mapper;
    protected $serviceManager;
    protected $options;
    protected $event;
    protected $innerStorage;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->options        = new \UnicaenAuth\Options\ModuleOptions();
        $this->serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager', ['get']);
        $this->innerStorage   = $this->getMock('Zend\Authentication\Storage\Session', ['write', 'clear', 'read']);
        $this->mapper         = $this->getMock('UnicaenApp\Mapper\Ldap\People', ['findOneByUsername']);
        $this->event          = new ChainEvent();
        $this->storage        = new Ldap();

        $this->storage->setMapper($this->mapper)
                      ->setServiceManager($this->serviceManager)
                      ->setStorage($this->innerStorage);
    }

    public function testCanRetrieveDefaultInnerStorage()
    {
        $storage = new Ldap();
        $this->assertInstanceOf('Zend\Authentication\Storage\StorageInterface', $storage->getStorage());
    }

    public function testCanRetrieveMapperFromLdapService()
    {
        $this->serviceManager->expects($this->once())
                             ->method('get')
                             ->with('ldap_people_mapper')
                             ->will($this->returnValue('result'));

        $this->storage->setMapper(null);
        $this->assertEquals('result', $this->storage->getMapper());
    }

    public function testCanRetrieveOptionsFromServiceManager()
    {
        $this->serviceManager->expects($this->once())
                             ->method('get')
                             ->with('unicaen-auth_module_options')
                             ->will($this->returnValue($options = new \UnicaenAuth\Options\ModuleOptions()));

        $this->assertSame($options, $this->storage->getOptions());
    }

    public function testCanWrite()
    {
        $this->event->setParam('contents', 12);

        $this->innerStorage->expects($this->once())
                     ->method('write')
                     ->with(12);

        $this->storage->write($this->event);
    }

    public function testCanClear()
    {
        $this->innerStorage->expects($this->once())
                           ->method('clear');

        $this->storage->clear($this->event);
    }

    public function testReadingReturnsNullIfInnerStorageIsEmpty()
    {
        $this->innerStorage->expects($this->once())
                     ->method('read')
                     ->will($this->returnValue(null));
        $this->mapper->expects($this->never())
                     ->method('findOneByUsername');

        $this->storage->read($this->event);
        $this->assertEquals(['ldap' => null], $this->event->getContents());
    }

    public function testReadingReturnsNullIfNoUserFound()
    {
        $this->innerStorage->expects($this->once())
                     ->method('read')
                     ->will($this->returnValue(12));
        $this->mapper->expects($this->once())
                     ->method('findOneByUsername')
                     ->will($this->returnValue(null));

        $this->storage->read($this->event);
        $this->assertEquals(['ldap' => null], $this->event->getContents());
    }

    public function getException()
    {
        return [
            [new \Zend\Ldap\Exception\LdapException()],
            [new \UnicaenApp\Exception()],
        ];
    }

    /**
     * @dataProvider getException
     */
    public function testReadingReturnsNullIfFindByUsernameThrowsException($exception)
    {
        $this->innerStorage->expects($this->once())
                     ->method('read')
                     ->will($this->returnValue(12));
        $this->mapper->expects($this->once())
                     ->method('findOneByUsername')
                     ->will($this->throwException($exception));

        $this->storage->read($this->event);
        $this->assertEquals(['ldap' => null], $this->event->getContents());
    }

    public function testReadingReturnsEntityIfUserFoundByUsername()
    {
        $entity = new People(PeopleTestAsset::$data1);

        $this->innerStorage->expects($this->once())
                     ->method('read')
                     ->will($this->returnValue('username'));
        $this->mapper->expects($this->once())
                     ->method('findOneByUsername')
                     ->will($this->returnValue($entity));

        $this->storage->read($this->event);
        $this->assertEquals(['ldap' => new AuthPeople($entity)], $this->event->getContents());
    }

    public function testReadingReturnsEntityInInnerStorageWithoutFetching()
    {
        $entity = new People(PeopleTestAsset::$data1);

        $this->innerStorage->expects($this->once())
                     ->method('read')
                     ->will($this->returnValue($entity));
        $this->mapper->expects($this->never())
                     ->method('findOneByUsername');

        $this->storage->read($this->event);
        $this->assertEquals(['ldap' => new AuthPeople($entity)], $this->event->getContents());
    }

    public function testReadingReturnsResolvedEntityWithoutReadingInnerStorage()
    {
        $entity = new People(PeopleTestAsset::$data1);

        $this->innerStorage->expects($this->once())
                     ->method('read')
                     ->will($this->returnValue(12));
        $this->mapper->expects($this->once())
                     ->method('findOneByUsername')
                     ->will($this->returnValue($entity));

        $firstResult = $this->storage->read($this->event);

        $this->innerStorage->expects($this->never())
                     ->method('read');

        $nextResult = $this->storage->read($this->event);

        $this->assertSame($firstResult, $nextResult);
    }
}