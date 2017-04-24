<?php
namespace UnicaenAuthTest\Acl;

use PHPUnit_Framework_TestCase;
use UnicaenAuth\Acl\NamedRole;

/**
 * Description of NamedRoleTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NamedRoleTest extends PHPUnit_Framework_TestCase
{
    public function testCanConstructWithoutName()
    {
        $role = new NamedRole('role-id', null);
        $this->assertEquals($role->getRoleId(), $role->getRoleName());
    }
    
    public function testCanConstructWithName()
    {
        $role = new NamedRole('role-id', null, $name = "Role name");
        $this->assertEquals($name, $role->getRoleName());
    }
    
    public function testCanSetName()
    {
        $role = new NamedRole('role-id', null, "Role name");
        $role->setRoleName($name = "New role name");
        $this->assertEquals($name, $role->getRoleName());
    }
    
    public function testCanSetDescription()
    {
        $role = new NamedRole('role-id', null, null, "Role description");
        $role->setRoleDescription($desc = "New role description");
        $this->assertEquals($desc, $role->getRoleDescription());
    }
}