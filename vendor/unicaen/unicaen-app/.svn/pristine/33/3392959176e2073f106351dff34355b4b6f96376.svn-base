<?php
namespace UnicaenAppTest\Entity\Ldap;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_Error;
use UnicaenApp\Entity\Ldap\Group;
use UnicaenAppTest\Entity\Ldap\TestAsset\Group as TestAssetPeople;

/**
 * Tests concernant la classe d'entité LDAP des structures.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class GroupTest extends PHPUnit_Framework_TestCase
{    
    public function provideGetInstancesValidData()
    {
        return array(
            'a' => array(array(TestAssetPeople::$data1)),
            'b' => array(array(TestAssetPeople::$data1, TestAssetPeople::$data2)),
        );
    }
    
    public function provideGetInstancesInvalidData()
    {
        $data = array( // NB: pas de 'dn'
            'cn'                  => 'admin_reseau',
            'description'         => "Administrateurs réseau de la DSI",
            'supanngroupedatefin' => '999912310000Z',
        );
        return array(
            'a' => array(null),
            'b' => array('data'),
            'c' => array(array()),
            'd' => array(new Group(TestAssetPeople::$data1)),
            'e' => array(TestAssetPeople::$data1),
            'f' => array(array($data)),
        );
    }
    
    /**
     * @dataProvider provideGetInstancesValidData
     */
    public function testGetInstancesWithValidData($data)
    {
        $entities = Group::getInstances($data);
        $this->assertInternalType('array', $entities);
        $this->assertCount(count($data), $entities);
        $this->assertContainsOnly('string', array_keys($entities));
        $this->assertContainsOnlyInstancesOf('\UnicaenApp\Entity\Ldap\Group', $entities);
    }
    
    /**
     * @dataProvider provideGetInstancesInvalidData
     * @expectedException \InvalidArgumentException
     */
    public function testGetInstancesWithInvalidData($data)
    {
        Group::getInstances($data);
    }
    
    public function provideConstructorValidData()
    {
        return array(
            'a' => array(TestAssetPeople::$data1),
            'b' => array(TestAssetPeople::$data2),
        );
    }
    
    public function provideConstructorInvalidData()
    {
        $data = array( // NB: pas de 'dn'
            'cn'                  => 'admin_reseau',
            'description'         => "Administrateurs réseau de la DSI",
            'supanngroupedatefin' => '999912310000Z',
        );
        return array(
            'a' => array($data),
        );
    }
    
    /**
     * @dataProvider provideConstructorValidData
     */
    public function testConstructWithValidData($data)
    {
        $entity = new Group($data);
        $this->assertInstanceOf('\UnicaenApp\Entity\Ldap\Group', $entity);
    }
    
    /**
     * @dataProvider provideConstructorInvalidData
     * @expectedException \InvalidArgumentException
     */
    public function testConstructWithInvalidData($data)
    {
        new Group($data);
    }
    
    /**
     * @depends testConstructWithValidData
     * @dataProvider provideConstructorValidData
     */
    public function testGettingDataWithoutSpecificKeyReturnsDataArray($data)
    {
        $entity = new Group($data);
        $entityData = $entity->getData();
        $this->assertEquals($data, $entityData);
    }
    
    /**
     * @depends testConstructWithValidData
     * @dataProvider provideConstructorValidData
     */
    public function testGettingDataWithSpecificKeyReturnsKeyValue($data)
    {
        $entity = new Group($data);
        $value = $entity->getData($key = 'dn');
        $this->assertEquals($data[$key], $value);
    }
    
    /**
     * @depends testConstructWithValidData
     * @dataProvider provideConstructorValidData
     * @expectedException \UnicaenApp\Exception\LogicException
     */
    public function testGettingDataWithNonExistingSpecificKeyThrowsException($data)
    {
        $entity = new Group($data);
        $entity->getData('nonexistingspecifickey');
    }
    
    /**
     * @depends testConstructWithValidData
     */
    public function testGetters()
    {
        $entity = new Group(TestAssetPeople::$data1);
        $this->assertEquals(TestAssetPeople::$data1['description'], $entity->getDescription());
        $this->assertEquals(TestAssetPeople::$data1['dn'], $entity->getDn());
        $this->assertEquals(TestAssetPeople::$data1['cn'], $entity->getCn());
        $this->assertEquals(TestAssetPeople::$data1['member'], $entity->getMember());
        $expectedDate = new \DateTime(TestAssetPeople::$data1['supanngroupedatefin']);
        $this->assertEquals($expectedDate->getTimestamp(), $entity->getSupannGroupeDateFin()->getTimestamp());
        
        $entity = new Group(TestAssetPeople::$data2);
        $this->assertEquals(TestAssetPeople::$data2['description'], $entity->getDescription());
        $this->assertEquals(TestAssetPeople::$data2['dn'], $entity->getDn());
        $this->assertEquals(TestAssetPeople::$data2['cn'], $entity->getCn());
        $this->assertEquals(TestAssetPeople::$data2['member'], $entity->getMember());
        $expectedDate = new \DateTime(TestAssetPeople::$data2['supanngroupedatefin']);
        $this->assertEquals($expectedDate->getTimestamp(), $entity->getSupannGroupeDateFin()->getTimestamp());
    }
    
    public function testToStringReturnsString()
    {
        $entity = new Group(TestAssetPeople::$data1);
        try {
            $toString = "" . $entity;
        } 
        catch (PHPUnit_Framework_Error $e) {
            $this->fail($e->getMessage());
        }
        $this->assertNotEmpty($toString);
    }
}