<?php
namespace UnicaenAuthTest\Provider\Identity;

use PHPUnit_Framework_TestCase;
use UnicaenAuth\Provider\Identity\Chain;
use UnicaenAuth\Acl\NamedRole;

/**
 * Description of ChainTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ChainTest extends PHPUnit_Framework_TestCase
{
    protected $provider;
    protected $authorize;
    protected $authService;
    protected $serviceManager;
    protected $eventManager;
    protected $event;
    protected $acl;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->authorize      = $this->getMock('BjyAuthorize\Service\Authorize', ['getAcl'], [], '', false);
        $this->serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager', ['get']);
        $this->authService    = $this->getMock('Zend\Authentication\AuthenticationService', ['getIdentity']);
        $this->provider       = new Chain($this->authService);
        $this->eventManager   = $this->getMock('Zend\EventManager\EventManager', ['trigger']);
        $this->event          = $this->getMock('UnicaenAuth\Provider\Identity\ChainEvent', ['getRoles']);
        $this->acl            = $this->getMock('Zend\Permissions\Acl\Acl', ['hasRole', 'getRole']);

        $this->authorize->expects($this->any())
                ->method('getAcl')
                ->will($this->returnValue($this->acl));

        $this->serviceManager->expects($this->any())
                ->method('get')
                ->will($this->returnValue($this->authorize));

        $this->provider->setServiceLocator($this->serviceManager)
                ->setEventManager($this->eventManager)
                ->setEvent($this->event);
    }

    public function testCanRetrieveDefaultEventManager()
    {
        $provider = new Chain($this->authService);
        $this->assertInstanceOf('Zend\EventManager\EventManager', $provider->getEventManager());
    }

    public function testCanRetrieveDefaultEvent()
    {
        $provider = new Chain($this->authService);
        $this->assertInstanceOf('UnicaenAuth\Provider\Identity\ChainEvent', $event = $provider->getEvent());
        $this->assertSame($provider, $event->getTarget());
    }

    public function testCanSetServiceLocator()
    {
        $this->assertSame($this->serviceManager, $this->provider->getServiceLocator());
    }

    public function testGettingIdentityRolesReturnsEmptyArrayWhenZeroRolesCollected()
    {
        $this->event->expects($this->once())
                ->method('getRoles')
                ->will($this->returnValue([]));

        $this->assertEquals([], $this->provider->getIdentityRoles());
        $this->assertEquals([], $this->readAttribute($this->provider, 'roles'));

        return $this->provider;
    }

    /**
     * @depends testGettingIdentityRolesReturnsEmptyArrayWhenZeroRolesCollected
     */
    public function testGettingIdentityRolesDoesNotCollectRolesTwice($provider)
    {
        $this->eventManager->expects($this->never())
                ->method('trigger');

        $this->assertEquals([], $provider->getIdentityRoles());
    }

    public function testGettingIdentityRolesDoesNotIncludeUnknownRoles()
    {
        $this->event->expects($this->once())
                ->method('getRoles')
                ->will($this->returnValue(['role 1']));

        $this->acl->expects($this->once())
                ->method('hasRole')
                ->with('role 1')
                ->will($this->returnValue(false));

        $this->assertEquals([], $this->provider->getIdentityRoles());
    }

    public function testGettingIdentityRolesDoesNotIncludeSameRoleTwice()
    {
        $this->event->expects($this->once())
                ->method('getRoles')
                ->will($this->returnValue(['role 1', 'role 1']));

        $this->acl->expects($this->exactly(2))
                ->method('hasRole')
                ->will($this->returnValue(true));

        $this->acl->expects($this->exactly(1))
                ->method('getRole')
                ->will($this->returnValue($role = new NamedRole('role 1')));

        $this->assertEquals(['role 1' => $role], $this->provider->getIdentityRoles());
    }
}