<?php
namespace UnicaenAppTest\Mapper\Ldap;

use UnicaenApp\Mapper\Ldap\Group;
use UnicaenAppTest\Mapper\Ldap\TestAsset\Group as TestAssetPeople;

/**
 * Classe de test du mapper LDAP des structures.
 *
 * @property Group $mapper
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class GroupTest extends CommonTest
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->entityClassName = '\UnicaenApp\Entity\Ldap\Group';
        $this->mapper          = new Group($this->ldap);
    }
    
    public function testFindOneByDnReturnsCorrespondingGroup()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1)));
        
        $dn = TestAssetPeople::$data1['dn'][0];
        $entry = $this->mapper->findOneByDn($dn);
        $this->assertInstanceOf($this->entityClassName, $entry);
        $this->assertEquals($dn, $entry->getDn());
    }
    
    public function testFindOneByDnReturnsNullWhenNoEntryFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $dn = 'cn=unexisting,ou=groups,dc=unicaen,dc=fr';
        $entry = $this->mapper->findOneByDn($dn);
        $this->assertNull($entry);
    }
    
    public function testFindOneByDnReturnsNullWhenLdapExceptionIsThrown()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->throwException(new \Zend\Ldap\Exception\LdapException($this->ldap, "Exception!")));
        
        $dn = 'cn=peu-importe,ou=groups,dc=unicaen,dc=fr';
        $entry = $this->mapper->findOneByDn($dn);
        $this->assertNull($entry);
    }
    
    public function testFindAllByDnReturnsCorrespondingGroups()
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(TestAssetPeople::$data1), array(TestAssetPeople::$data2)));
        
        $dn = array(TestAssetPeople::$data1['dn'][0], TestAssetPeople::$data2['dn'][0]);
        $entries = $this->mapper->findAllByDn($dn);
        $this->assertInternalType('array', $entries);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $entries);
        $this->assertContainsOnly('string', array_keys($entries));
        $this->assertCount(count($dn), $entries);
    }
    
    public function testFindAllByDnReturnsEmptyArrayWhenNoEntryFound()
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(), array()));
        
        $dn = array('cn=peu-importe,ou=groups,dc=unicaen,dc=fr', 'cn=peu-importe,ou=groups,dc=unicaen,dc=fr');
        $entries = $this->mapper->findAllByDn($dn);
        $this->assertInternalType('array', $entries);
        $this->assertCount(0, $entries);
    }
    
    public function testFindAllReturnsCorrespondingGroups()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
//        $dn = array(TestAssetPeople::$data1['dn'][0], TestAssetPeople::$data2['dn'][0]);
        $entries = $this->mapper->findAll();
        $this->assertInternalType('array', $entries);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $entries);
        $this->assertContainsOnly('string', array_keys($entries));
        $this->assertCount(2, $entries);
    }
    
    public function testFindOneByCnReturnsCorrespondingGroup()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1)));
        
        $cn = TestAssetPeople::$data1['cn'][0];
        $entry = $this->mapper->findOneByCn($cn);
        $this->assertInstanceOf($this->entityClassName, $entry);
        $this->assertEquals($cn, $entry->getCn());
    }
    
    public function testFindOneByCnReturnsNullWhenNoEntryFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $cn = 'unexisting';
        $entry = $this->mapper->findOneByCn($cn);
        $this->assertNull($entry);
    }
    
    public function testFindAllByCnReturnsCorrespondingGroups()
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $cn = array(TestAssetPeople::$data1['cn'][0], TestAssetPeople::$data2['cn'][0]);
        $entries = $this->mapper->findAllByCn($cn);
        $this->assertInternalType('array', $entries);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $entries);
        $this->assertContainsOnly('string', array_keys($entries));
        $this->assertCount(count($cn), $entries);
    }
    
    public function testFindAllByCnReturnsEmptyArrayWhenNoEntryFound()
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $cn = array('unexisting', 'unexisting');
        $entries = $this->mapper->findAllByCn($cn);
        $this->assertInternalType('array', $entries);
        $this->assertCount(0, $entries);
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\LogicException
     */
    public function testFilterGroupsByEndDateWithNoMapperSpecifiedThrowsException()
    {
        // groups specified as strings
        Group::filterGroupsByDateFin('peu_importe_le_cn'); // no mapper specified!
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\RuntimeException
     */
    public function testFilterGroupsByEndDateThrowsExceptionIfGroupNotFound()
    {
        $mapper = $this->getMock('UnicaenApp\Mapper\Ldap\Group', array('findOneByCn'));
        $mapper->expects($this->once())
               ->method('findOneByCn')
               ->will($this->returnValue(null));
        
        // groups specified as strings
        Group::filterGroupsByDateFin('peu_importe_le_cn', null, $mapper);
    }
    
    public function testFilterStringGroupsByEndDateExcludesClosedGroups()
    {
        $dateObs = new \DateTime();
        
        $this->ldap->expects($this->exactly(2))
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(TestAssetPeople::$data3), array(TestAssetPeople::$data4))); // groupes finis au 14/01/2013
        
        $cn = array(TestAssetPeople::$data3['cn'][0], TestAssetPeople::$data4['cn'][0]);
        
        // groups specified as strings
        $filteredGroups = Group::filterGroupsByDateFin($cn, $dateObs, $this->mapper);
        $this->assertInternalType('array', $filteredGroups);
        $this->assertEmpty($filteredGroups);
    }
    
    public function testFilterStringGroupsByEndDateDoesNotExcludeUnclosedGroups()
    {
        $dateObs = new \DateTime();
        $dateObs->setDate(2011, 1, 1);
        
        $this->ldap->expects($this->exactly(2))
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(TestAssetPeople::$data3), array(TestAssetPeople::$data4))); // groupes finis au 14/01/2013
        
        $cn = array(TestAssetPeople::$data3['cn'][0], TestAssetPeople::$data4['cn'][0]);
        
        // groups specified as strings
        $filteredGroups = Group::filterGroupsByDateFin($cn, $dateObs, $this->mapper);
        $this->assertInternalType('array', $filteredGroups);
        $this->assertNotEmpty($filteredGroups);
    }
    
    public function testFilterEntityGroupsByEndDateExcludesClosedGroups()
    {
        $dateObs = new \DateTime();
        
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data3, TestAssetPeople::$data4))); // groupes finis au 14/01/2013
        
        $cn = array(TestAssetPeople::$data3['cn'][0], TestAssetPeople::$data4['cn'][0]);
        
        // groups specified as objects
        $groups = $this->mapper->findAllByCn($cn);
        $filteredGroups = Group::filterGroupsByDateFin($groups, $dateObs);
        $this->assertInternalType('array', $filteredGroups);
        $this->assertEmpty($filteredGroups);
    }
    
    public function testFilterEntityGroupsByEndDateDoesNotExcludeUnclosedGroups()
    {
        $dateObs = new \DateTime();
        $dateObs->setDate(2011, 1, 1);
        
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data3, TestAssetPeople::$data4))); // groupes finis au 14/01/2013
        
        $cn = array(TestAssetPeople::$data3['cn'][0], TestAssetPeople::$data4['cn'][0]);
        
        // groups specified as objects
        $groups = $this->mapper->findAllByCn($cn);
        $filteredGroups = Group::filterGroupsByDateFin($groups, $dateObs);
        $this->assertInternalType('array', $filteredGroups);
        $this->assertNotEmpty($filteredGroups);
    }
}