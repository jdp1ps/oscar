<?php
namespace UnicaenAppTest\Entity\Ldap;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_Error;
use UnicaenApp\Entity\Ldap\Structure;
use UnicaenAppTest\Entity\Ldap\TestAsset\Structure as TestAssetPeople;

/**
 * Tests concernant la classe d'entité LDAP des structures.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class StructureTest extends PHPUnit_Framework_TestCase
{    
    public function provideGetInstancesValidData()
    {
        return array(
            'a' => array(array(TestAssetPeople::$data1)),
            'b' => array(array(TestAssetPeople::$data1, TestAssetPeople::$data2)),
            'c' => array(array(TestAssetPeople::$data1, TestAssetPeople::$data2, TestAssetPeople::$data3)),
        );
    }
    
    /**
     * @dataProvider provideGetInstancesValidData
     */
    public function testGetInstancesWithValidData($data)
    {
        $entities = Structure::getInstances($data);
        $this->assertInternalType('array', $entities);
        $this->assertCount(count($data), $entities);
        $this->assertContainsOnly('string', array_keys($entities));
        $this->assertContainsOnlyInstancesOf('\UnicaenApp\Entity\Ldap\Structure', $entities);
    }
    
    public function provideGetInstancesInvalidData()
    {
        $data1 = array( // NB: pas de 'supanncodeentite'
            'dn'          => "supannCodeEntite=HS_G72J2,ou=structures,dc=unicaen,dc=fr",
            'ou'          => "Recrutement BIATSS",
            'description' => "Recrutement BIATSS",
        );
        $data2 = array( // NB: pas de 'dn'
            'supanncodeentite' => "HS_G72J2",
            'ou'               => "Recrutement BIATSS",
            'description'      => "Recrutement BIATSS",
        );
        return array(
            'notAnArray'         => array('string'),
            'emptyArray'         => array(array()),
            'structureData'      => array(TestAssetPeople::$data1),
            'noSupanncodeentite' => array(array($data1)),
            'noDn'               => array(array($data2)),
        );
    }
    
    /**
     * @dataProvider provideGetInstancesInvalidData
     * @expectedException \InvalidArgumentException
     */
    public function testGetInstancesWithInvalidData($data)
    {
        Structure::getInstances($data);
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
        $data = array( // NB: pas de 'supanncodeentite'
            'dn'                     => "supannCodeEntite=HS_G72J2,ou=structures,dc=unicaen,dc=fr",
            'ou'                     => "Recrutement BIATSS",
            'description'            => "Recrutement BIATSS",
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
        $entity = new Structure($data);
        $this->assertInstanceOf('\UnicaenApp\Entity\Ldap\Structure', $entity);
    }
    
    /**
     * @dataProvider provideConstructorInvalidData
     * @expectedException \InvalidArgumentException
     */
    public function testConstructWithInvalidData($data)
    {
        new Structure($data);
    }
    
    /**
     * @depends testConstructWithValidData
     */
    public function testGetters()
    {
        $entity = new Structure(TestAssetPeople::$data1);
        $this->assertEquals(TestAssetPeople::$data1['description'], $entity->getDescription());
        $this->assertEquals(TestAssetPeople::$data1['dn'], $entity->getDn());
        $this->assertEquals(TestAssetPeople::$data1['ou'], $entity->getOu());
        $this->assertEquals(TestAssetPeople::$data1['postaladdress'], $entity->getPostaladdress());
        $this->assertEquals(TestAssetPeople::$data1['supanncodeentite'], $entity->getSupanncodeentite());
        $this->assertEquals(TestAssetPeople::$data1['supanntypeentite'], $entity->getSupanntypeentite());
        $this->assertEquals(TestAssetPeople::$data1['telephonenumber'], $entity->getTelephonenumber());
        $this->assertEquals(Structure::extractCodeStructureHarpege(TestAssetPeople::$data1['supanncodeentite']), $entity->getCStructure());
        $this->assertEquals($entity->getDescription(), $entity->getLibelleAnnuaire());
        $this->assertEquals($entity->getOu(), $entity->getLcStructure());
        $this->assertEquals($entity->getOu(), $entity->getLlStructure());
        $this->assertEquals(Structure::extractCodeStructureHarpege(TestAssetPeople::$data1['supanncodeentite']), $entity->getCStructure());
        $this->assertNull($entity->getSupanncodeentiteparent());
        $this->assertNull($entity->getFacSimileTelephoneNumber());
        $this->assertNull($entity->getCStructurePere());
        
        $entity = new Structure(TestAssetPeople::$data2);
        $this->assertEquals(TestAssetPeople::$data2['description'], $entity->getDescription());
        $this->assertEquals(TestAssetPeople::$data2['dn'], $entity->getDn());
        $this->assertEquals(TestAssetPeople::$data2['facsimiletelephonenumber'], $entity->getFacSimileTelephoneNumber());
        $this->assertEquals(TestAssetPeople::$data2['ou'], $entity->getOu());
        $this->assertEquals(TestAssetPeople::$data2['postaladdress'], $entity->getPostaladdress());
        $this->assertEquals(TestAssetPeople::$data2['supanncodeentite'], $entity->getSupanncodeentite());
        $this->assertEquals(TestAssetPeople::$data2['supanncodeentiteparent'], $entity->getSupanncodeentiteparent());
        $this->assertEquals(TestAssetPeople::$data2['supanntypeentite'], $entity->getSupanntypeentite());
        $this->assertEquals(TestAssetPeople::$data2['telephonenumber'], $entity->getTelephonenumber());
        $this->assertEquals(Structure::extractCodeStructureHarpege(TestAssetPeople::$data2['supanncodeentite']), $entity->getCStructure());
        $this->assertEquals(Structure::extractCodeStructureHarpege(TestAssetPeople::$data2['supanncodeentiteparent']), $entity->getCStructurePere());
        $this->assertEquals($entity->getDescription(), $entity->getLibelleAnnuaire());
        $this->assertEquals($entity->getOu(), $entity->getLcStructure());
        $this->assertEquals($entity->getOu(), $entity->getLlStructure());
    }
    
    public function testToString()
    {
        $entity = new Structure(TestAssetPeople::$data1);
        try {
            $toString = "" . $entity;
        } 
        catch (PHPUnit_Framework_Error $e) {
            $this->fail($e->getMessage());
        }
        $this->assertNotEmpty($toString);
    }
    
    public function provideCodeStructureHarpegeValid()
    {
        return array(
            'a' => array('HS_C68', 'C68'),
            'b' => array('HS_800', '800'),
            'c' => array('HS_', null),
        );
    }
    
    public function provideCodeStructureHarpegeInvalid()
    {
        return array(
            'a' => array(null),
            'b' => array(''),
            'c' => array(array('HS_C68', 'HS_800')),
        );
    }
    
    /**
     * @dataProvider provideCodeStructureHarpegeValid
     */
    public function testExtractCodeStructureHarpegeValid($codeSupann, $codeHarpege)
    {
        $result = Structure::extractCodeStructureHarpege($codeSupann);
        $this->assertEquals($codeHarpege, $result);
    }
    
    /**
     * @dataProvider provideCodeStructureHarpegeInvalid
     * @expectedException \InvalidArgumentException
     */
    public function testExtractCodeStructureHarpegeInvalid($codeSupann)
    {
        Structure::extractCodeStructureHarpege($codeSupann);
    }
    
    public function provideCodesStructuresHarpegeForCreateFilter()
    {
        return array(
            'a' => array('C68',               '(supannCodeEntite=HS_C68)'),
            'b' => array(array('C68'),        '(supannCodeEntite=HS_C68)'),
            'c' => array(array('C68', '800'), '(|(supannCodeEntite=HS_C68)(supannCodeEntite=HS_800))'),
            'd' => array(null,                '(supannCodeEntite=HS_*)'),
            'd' => array('',                  '(supannCodeEntite=HS_*)'),
        );
    }
    
    /**
     * @dataProvider provideCodesStructuresHarpegeForCreateFilter
     */
    public function testCreateFilterForStructure($codesStructures, $filter)
    {
        $result = Structure::createFilterForStructure($codesStructures);
        $this->assertEquals($filter, $result);
    }
    
}
