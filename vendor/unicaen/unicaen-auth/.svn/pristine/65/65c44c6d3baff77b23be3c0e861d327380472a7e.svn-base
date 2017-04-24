<?php
namespace UnicaenAuthTest\Entity\Ldap;

use UnicaenApp\Entity\Ldap\People as BasePeople;
use UnicaenAuth\Entity\Ldap\People;
use UnicaenAppTest\Entity\Ldap\TestAsset\People as PeopleTestAsset;

/**
 * Description of PeopleTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class PeopleTest extends \PHPUnit_Framework_TestCase
{
    protected $entity;
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->entity = new People(PeopleTestAsset::$data1);
    }
    
    public function testCanConstructFromBasePeopleEntity()
    {
        new People(new BasePeople(PeopleTestAsset::$data1));
    }
    
    public function testSuperClassAndInterface()
    {
        $this->assertInstanceOf('UnicaenApp\Entity\Ldap\People', $this->entity);
        $this->assertInstanceOf('ZfcUser\Entity\UserInterface', $this->entity);
    }
    
    public function testGettingIdReturnsLdapUidAsDefaultValue()
    {
        $entity = new People(PeopleTestAsset::$data1);
        $this->assertEquals(PeopleTestAsset::$data1['uid'], $entity->getId());
    }
    
    public function testStateDependsOnDeactivatedLdapBranch()
    {
        $this->assertEquals(1, $this->entity->getState());
        
        $entity = new People(PeopleTestAsset::$dataDeactivated, 12);
        $this->assertEquals(0, $entity->getState());
    }
    
    /**
     * @expectedException \BadMethodCallException
     */
    public function testSettingStateThrowsException()
    {
        $this->entity->setState('whatever');
    }
    
    public function testGetDisplayNameReturnsString()
    {
        $this->assertInternalType('string', $this->entity->getDisplayName());
        $this->assertNotEmpty($this->entity->getDisplayName());
    }
    
    /**
     * @expectedException \BadMethodCallException
     */
    public function testSettingDisplayNameThrowsException()
    {
        $this->entity->setDisplayName('whatever');
    }
    
    public function testGetEmailReturnsString()
    {
        $this->assertInternalType('string', $this->entity->getEmail());
        $this->assertNotEmpty($this->entity->getEmail());
    }
    
    /**
     * @expectedException \BadMethodCallException
     */
    public function testSettingEmailThrowsException()
    {
        $this->entity->setEmail('whatever');
    }
    
    /**
     * @expectedException \BadMethodCallException
     */
    public function testSettingIdThrowsException()
    {
        $this->entity->setId('whatever');
    }
    
    public function testGetUsernameReturnsSupannAliasLogin()
    {
        $this->assertEquals(PeopleTestAsset::$data1['supannaliaslogin'], $this->entity->getUsername());
    }
    
    /**
     * @expectedException \BadMethodCallException
     */
    public function testSettingUsernameThrowsException()
    {
        $this->entity->setUsername('whatever');
    }
    
    public function testGetPasswordReturnsNull()
    {
        $entity = new People(PeopleTestAsset::$data1);
        $this->assertNull($entity->getPassword());
    }
    
    /**
     * @expectedException \BadMethodCallException
     */
    public function testSettingPasswordThrowsException()
    {
        $this->entity->setPassword('whatever');
    }
    
    public function testGettingRolesContainsMembershipAndSupannRole()
    {
        $this->assertEquals(
                array_merge($this->entity->getMemberOf(), $this->entity->getSupannRolesEntite()), 
                $this->entity->getRoles());
    }
}