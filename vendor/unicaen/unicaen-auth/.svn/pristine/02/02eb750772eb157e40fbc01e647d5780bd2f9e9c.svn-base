<?php

namespace UnicaenAuthTest\Entity\Db;

use UnicaenAuth\Entity\Db\Role;
use PHPUnit_Framework_TestCase;

/**
 * Description of UserTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class RoleTest extends PHPUnit_Framework_TestCase
{
    protected $role;
    
    protected function setUp()
    {
        $this->role = new Role();
    }
    
    public function testImplementsInterfaces()
    {
        $this->assertInstanceOf('BjyAuthorize\Acl\HierarchicalRoleInterface', $this->role);
    }
    
    public function testConstructorInitializeParent()
    {
        $this->assertNull($this->role->getParent());
    }
    
    public function testCanSetId()
    {
        $this->role->setId(12);
        $this->assertEquals(12, $this->role->getId());
    }
    
    public function testCanSetRoleId()
    {
        $this->role->setRoleId('content');
        $this->assertEquals('content', $this->role->getRoleId());
    }
    
    public function testCanSetIsDefault()
    {
        $this->role->setIsDefault(true);
        $this->assertEquals(true, $this->role->getIsDefault());
    }
    
    public function testCanSetParent()
    {
        $this->role->setParent($parent = new \UnicaenAuth\Entity\Db\Role());
        $this->assertEquals($parent, $this->role->getParent());
    }
    
    public function testCanGetObjectToString()
    {
        $this->role->setRoleId('content');
        $this->assertEquals('content', (string) $this->role);
    }
}