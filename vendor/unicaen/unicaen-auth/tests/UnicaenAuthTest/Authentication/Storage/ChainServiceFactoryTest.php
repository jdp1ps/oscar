<?php

namespace UnicaenAuthTest\Authentication\Storage;

use UnicaenAuth\Authentication\Storage\ChainServiceFactory;

/**
 * Description of ChainServiceFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ChainServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $serviceLocator;
    protected $eventManager;
    protected $factory;
    protected $events = ['read', 'write', 'clear'];

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->factory        = new ChainServiceFactory();
        $this->serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface', []);
        $this->ldapStorage    = $this->getMock('UnicaenAuth\Authentication\Storage\Ldap', $this->events);
        $this->dbStorage      = $this->getMock('UnicaenAuth\Authentication\Storage\Db', $this->events);
    }

    public function testCanCreateService()
    {
        $this->serviceLocator->expects($this->exactly(2))
                ->method('get')
                ->will($this->onConsecutiveCalls($this->ldapStorage, $this->dbStorage));

        $service = $this->factory->createService($this->serviceLocator);

        $this->assertInstanceOf('UnicaenAuth\Authentication\Storage\Chain', $service);

        foreach ($this->events as $event) {
            $listeners = $service->getEventManager()->getListeners($event); /* @var $listeners \Zend\Stdlib\SplPriorityQueue */
            $this->assertCount(2, $listeners);
        }
    }
}