<?php
namespace UnicaenAppTest\Mapper\Ldap;

use UnicaenApp\Mapper\Ldap\Structure;
use UnicaenAppTest\Mapper\Ldap\TestAsset\Structure as TestAssetStructure;
use UnicaenApp\Entity\Ldap\Structure as EntityStructure;

/**
 * Classe de test du mapper LDAP des structures.
 *
 * @property Structure $mapper
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class StructureTest extends CommonTest
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->entityClassName = '\UnicaenApp\Entity\Ldap\Structure';
        $this->mapper = new Structure($this->ldap);
    }
    
    public function testFindOneByDnReturnsEntity()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetStructure::$data1)));
        
        $dn = 'peu-importe';
        $structure = $this->mapper->findOneByDn($dn);
        $this->assertInstanceOf($this->entityClassName, $structure);
        $this->assertEquals(TestAssetStructure::$data1['dn'][0], $structure->getDn());
    }
    
    public function testFindOneByDnReturnsNullWhenEntryNotFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $dn = 'peu-importe';
        $structure = $this->mapper->findOneByDn($dn);
        $this->assertNull($structure);
    }
    
    public function testFindOneByCodeEntiteReturnsEntity()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetStructure::$data1)));
        
        $codeEntite = 'peu-importe';
        $structure = $this->mapper->findOneByCodeEntite($codeEntite);
        $this->assertInstanceOf($this->entityClassName, $structure);
        $this->assertEquals(TestAssetStructure::$data1['supanncodeentite'][0], $structure->getSupannCodeEntite());
    }
    
    public function testFindOneByCodeEntiteReturnsNullWhenEntryNotFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $codeEntite = 'peu-importe';
        $structure = $this->mapper->findOneByCodeEntite($codeEntite);
        $this->assertNull($structure);
    }
    
    public function getDnOrCodeEntite()
    {
        return array(
            array('supannCodeEntite=HS_C68,ou=structures,dc=unicaen,dc=fr'),
            array('HS_C68'),
        );
    }
    
    /**
     * @dataProvider getDnOrCodeEntite
     * @param string $dnOrCodeEntite
     */
    public function testFindOneByDnOrCodeEntiteReturnsEntity($dnOrCodeEntite)
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetStructure::$data1)));
        
        $structure = $this->mapper->findOneByDnOrCodeEntite($dnOrCodeEntite);
        $this->assertInstanceOf($this->entityClassName, $structure);
    }
    
    public function testFindOneByDnOrCodeEntiteReturnsNullWhenEntryNotFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $dnOrCodeEntite = 'peu-importe';
        $structure = $this->mapper->findOneByDnOrCodeEntite($dnOrCodeEntite);
        $this->assertNull($structure);
    }
    
    public function testFindOneByCodeStructureReturnsEntity()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetStructure::$data1)));
        
        $code = 'peu-importe';
        $result = $this->mapper->findOneByCodeStructure($code);
        $this->assertInstanceOf($this->entityClassName, $result);
    }
    
    public function testFindOneByCodeStructureReturnsNullWhenEntryNotFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $code = 'peu-importe';
        $structure = $this->mapper->findOneByCodeStructure($code);
        $this->assertNull($structure);
    }
    
    public function testFindAllByCodeStructureReturnsEntitiesArray()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetStructure::$data1, TestAssetStructure::$data2)));
        
        $codes = array('peu-importe', 'nevermind');
        $result = $this->mapper->findAllByCodeStructure($codes);
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnly('string', array_keys($result));
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
    }
    
    public function testFindAllByCodeStructureReturnsEmptyArrayWhenNoEntryFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $codes = array('peu-importe', 'nevermind');
        $result = $this->mapper->findAllByCodeStructure($codes);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }
    
    public function testCanFindStructureChildsByCodeEntite()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetStructure::$data1, TestAssetStructure::$data2)));
        
        $codes = array('peu-importe', 'nevermind');
        $result = $this->mapper->findStructureChildsByCodeEntite($codes);
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnly('string', array_keys($result));
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
    }
    
    public function testFindingStructureChildsWithNoCodeEntiteReturnsEmptyArray()
    {
        $this->ldap->expects($this->never())
                   ->method('searchEntries');
        
        $result = $this->mapper->findStructureChildsByCodeEntite(null);
        $this->assertEquals(array(), $result);
    }
    
    public function getCode()
    {
        $code = EntityStructure::extractCodeStructureHarpege(TestAssetStructure::$data2['supanncodeentite'][0]);
        return array(
            array(
                $code, 
                $includeRoot = true,
                $glue = ' > ',
                sprintf("%s > %s [%s]", TestAssetStructure::$data1['ou'][0], TestAssetStructure::$data2['ou'][0], $code)),
            array(
                $code, 
                $includeRoot = false, 
                $glue,
                sprintf("%s [%s]", TestAssetStructure::$data2['ou'][0], $code)),
        );
    }
    
    /**
     * @dataProvider getCode
     */
    public function testFindOnePathRootIncludedReturnsString($code, $includeRoot, $glue, $expectedResult)
    {
        $this->ldap->expects($this->exactly(2))
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(TestAssetStructure::$data2), array(TestAssetStructure::$data1)));
        
        $result = $this->mapper->findOnePathByCodeStructure($code, $includeRoot, false, $glue);
        $this->assertEquals($expectedResult, $result);
    }
    
    public function testFindOnePathReturnsMessageWhenNoEntryFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $code = 'peu-importe';
        $result = $this->mapper->findOnePathByCodeStructure($code);
        $this->assertEquals(Structure::CHEMIN_INTROUVABLE, $result);
    }
    
    public function testFindAllPathReturnsEntitiesArray()
    {
        $this->ldap->expects($this->exactly(4))
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(
                           array(TestAssetStructure::$data1, TestAssetStructure::$data2), // recherche des structures 2 et 1 spécifiées
                           array(TestAssetStructure::$data1), // recherche chemin structure 1 (une seule itération car de niveau 1)
                           array(TestAssetStructure::$data2),   // recherche chemin structure 2 (première itération car de niveau 2)
                           array(TestAssetStructure::$data1))); // recherche chemin structure 2 (seconde itération car de niveau 2)
        
        $codes = array(
            EntityStructure::extractCodeStructureHarpege(TestAssetStructure::$data2['supanncodeentite'][0]), 
            EntityStructure::extractCodeStructureHarpege(TestAssetStructure::$data1['supanncodeentite'][0]));
        $tmp = $result = $this->mapper->findAllPathByCodeStructure($codes, false, false, true);
        $this->assertInternalType('array', $result);
        $this->assertCount(count($codes), $result);
        $this->assertEquals($codes, array_keys($result));
        $this->assertContainsOnly('string', $result, true);
        $this->assertNotContains(Structure::CHEMIN_INTROUVABLE, $result);
        foreach ($result as $key => $value) {
            $this->assertStringEndsWith(" [$key]", $value);
        }
        asort($tmp);
        $this->assertEquals($tmp, $result);
    }
    
    public function testFindAllPathWithReturnsMessageWhenNoEntryFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $codes = 'peu-importe';
        $result = $this->mapper->findAllPathByCodeStructure($codes);
        $this->assertEquals(Structure::CHEMIN_INTROUVABLE, $result);
    }
}