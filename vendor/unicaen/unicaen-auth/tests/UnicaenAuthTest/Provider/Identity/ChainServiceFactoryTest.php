<?php

namespace UnicaenAuthTest\Provider\Identity;

use PHPUnit_Framework_TestCase;
use UnicaenAuth\Provider\Identity\ChainServiceFactory;

/**
 * Description of ChainServiceFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ChainServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $serviceLocator;
    protected $eventManager;
    protected $factory;
    protected $events = ['getIdentityRoles'];

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->factory        = new ChainServiceFactory();
        $this->serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface', []);
        $this->ldapProvider   = $this->getMock('UnicaenAuth\Provider\Identity\Ldap', $this->events, [], '', false);
        $this->dbProvider     = $this->getMock('UnicaenAuth\Provider\Identity\Db', $this->events, [], '', false);
    }

    public function testCanCreateService()
    {
        $this->serviceLocator->expects($this->exactly(2))
                ->method('get')
                ->will($this->onConsecutiveCalls($this->dbProvider, $this->ldapProvider));

        $service = $this->factory->createService($this->serviceLocator);

        $this->assertInstanceOf('UnicaenAuth\Provider\Identity\Chain', $service);

        foreach ($this->events as $event) {
            $listeners = $service->getEventManager()->getListeners($event); /* @var $listeners \Zend\Stdlib\SplPriorityQueue */
            $this->assertCount(2, $listeners);
        }
    }
}