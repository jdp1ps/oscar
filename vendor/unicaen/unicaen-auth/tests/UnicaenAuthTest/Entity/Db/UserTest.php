<?php

namespace UnicaenAuthTest\Entity\Db;

use UnicaenAuth\Entity\Db\User;
use PHPUnit_Framework_TestCase;

/**
 * Description of UserTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    protected $user;

    protected function setUp()
    {
        $this->user = new User();
    }

    public function testImplementsInterfaces()
    {
        $this->assertInstanceOf('ZfcUser\Entity\UserInterface', $this->user);
        $this->assertInstanceOf('BjyAuthorize\Provider\Role\ProviderInterface', $this->user);
    }

    public function testConstructorInitializeRoles()
    {
        $this->assertEquals([], $this->user->getRoles());
    }

    public function testCanSetId()
    {
        $this->user->setId(12);
        $this->assertEquals(12, $this->user->getId());
    }

    public function testCanSetUsername()
    {
        $this->user->setUsername('content');
        $this->assertEquals('content', $this->user->getUsername());
    }

    public function testCanSetEmail()
    {
        $this->user->setEmail('content');
        $this->assertEquals('content', $this->user->getEmail());
    }

    public function testCanSetDisplayName()
    {
        $this->user->setDisplayName('content');
        $this->assertEquals('content', $this->user->getDisplayName());
    }

    public function testCanSetPassword()
    {
        $this->user->setPassword('content');
        $this->assertEquals('content', $this->user->getPassword());
    }

    public function testCanSetState()
    {
        $this->user->setState(1);
        $this->assertEquals(1, $this->user->getState());
    }

    public function testCanAddRole()
    {
        $roles = new \Doctrine\Common\Collections\ArrayCollection([
                new \UnicaenAuth\Entity\Db\Role(),
                new \UnicaenAuth\Entity\Db\Role(),
        ]);
        $this->user->addRole($roles[0]);
        $this->user->addRole($roles[1]);
        $this->assertEquals($roles->getValues(), $this->user->getRoles());
    }

    public function testCanGetObjectToString()
    {
        $this->user->setDisplayName('content');
        $this->assertEquals('content', (string) $this->user);
    }
}