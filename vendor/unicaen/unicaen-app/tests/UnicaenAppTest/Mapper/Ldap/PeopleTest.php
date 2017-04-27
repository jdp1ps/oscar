<?php
namespace UnicaenAppTest\Mapper\Ldap;

use PHPUnit_Framework_Constraint_IsAnything;
use PHPUnit_Framework_Constraint_StringContains;
use UnicaenApp\Entity\Ldap\Group as EntityGroup;
use UnicaenApp\Entity\Ldap\Structure as EntityStructure;
use UnicaenApp\Exception;
use UnicaenApp\Mapper\Ldap\People;
use UnicaenApp\Mapper\Ldap\Structure as MapperStructure;
use UnicaenAppTest\Entity\Ldap\TestAsset\Group as TestAssetEntityGroup;
use UnicaenAppTest\Entity\Ldap\TestAsset\Structure as TestAssetEntityStructure;
use UnicaenAppTest\Mapper\Ldap\TestAsset\People as TestAssetPeople;
use UnicaenAppTest\Mapper\Ldap\TestAsset\Structure as TestAssetStructure;

/**
 * Classe de test du mapper LDAP des individus.
 *
 * @property People $mapper
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class PeopleTest extends CommonTest
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->entityClassName = '\UnicaenApp\Entity\Ldap\People';
        $this->mapper = new People($this->ldap);
    }
    
    public function testGettingStructureMapperReturnsDefaultOne()
    {
        $mapper = new People($this->ldap);
        $this->assertInstanceOf('UnicaenApp\Mapper\Ldap\Structure', $mapper->getMapperStructure());
    }
    
    public function testSettingStructureMapperPropagatesLdapObjectToIt()
    {
        $this->mapper->setMapperStructure(new MapperStructure($this->ldap));
        $this->assertSame($this->mapper->getLdap(), $this->mapper->getMapperStructure()->getLdap());
    }
    
    public function testFindOneByUidReturnsEntity()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1)));
        
        $uid = 'peu-importe';
        $result = $this->mapper->findOneByUid($uid);
        $this->assertInstanceOf($this->entityClassName, $result);
        $this->assertEquals(TestAssetPeople::$data1['dn'][0], $result->getDn());
        $this->assertFalse($result->estDesactive());
    }
    
    public function testFindOneByUidReturnsNullWhenNoEntryFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $uid = 'unexisting';
        $result = $this->mapper->findOneByUid($uid);
        $this->assertNull($result);
    }
    
    public function testFindOneByUidTriesDeactivatedBranchAndReturnsEntity()
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(), array(TestAssetPeople::$dataDeactivated)));
        
        $uid = 'peu-importe';
        $result = $this->mapper->findOneByUid($uid, true);
        $this->assertInstanceOf($this->entityClassName, $result);
        $this->assertTrue($result->estDesactive());
    }
    
    public function testFindOneByUidTriesDeactivatedBranchAndReturnsNullWhenNoEntryFound()
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(), array()));
        
        $uid = 'unexisting';
        $result = $this->mapper->findOneByUid($uid, true);
        $this->assertNull($result);
    }

    public function testFindByNoIndividuReturnsEntity()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1)));
        
        $noIndividu = '390';
        $result = $this->mapper->findOneByNoIndividu($noIndividu);
        $this->assertInstanceOf($this->entityClassName, $result);
        $this->assertFalse($result->estDesactive());
    }
    
    public function testFindByNoIndividuTriesDeactivatedBranchAndReturnsEntity()
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(), array(TestAssetPeople::$dataDeactivated)));
        
        $noIndividu = '390';
        $result = $this->mapper->findOneByNoIndividu($noIndividu, true);
        $this->assertInstanceOf($this->entityClassName, $result);
        $this->assertTrue($result->estDesactive());
    }
    
    public function testFindByNoIndividuReturnsNullWhenNoEntryFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $noIndividu = 'unexisting';
        $result = $this->mapper->findOneByNoIndividu($noIndividu);
        $this->assertNull($result);
    }
    
    public function testFindByNoIndividuTriesDeactivatedBranchAndReturnsNullWhenNoEntryFound()
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(), array()));
        
        $noIndividu = 'unexisting';
        $result = $this->mapper->findOneByNoIndividu($noIndividu, true);
        $this->assertNull($result);
    }

    public function testFindByUsernameReturnsEntity()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1)));
        
        $username = 'login';
        $result = $this->mapper->findOneByUsername($username);
        $this->assertInstanceOf($this->entityClassName, $result);
        $this->assertFalse($result->estDesactive());
    }
    
    public function testFindByUsernameTriesDeactivatedBranchAndReturnsEntity()
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(), array(TestAssetPeople::$dataDeactivated)));
        
        $username = 'login';
        $result = $this->mapper->findOneByUsername($username, true);
        $this->assertInstanceOf($this->entityClassName, $result);
        $this->assertTrue($result->estDesactive());
    }
    
    public function testFindByUsernameReturnsNullWhenNoEntryFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $username = 'unexisting';
        $result = $this->mapper->findOneByUsername($username);
        $this->assertNull($result);
    }
    
    public function testFindByUsernameTriesDeactivatedBranchAndReturnsNullWhenNoEntryFound()
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(), array()));
        
        $username = 'unexisting';
        $result = $this->mapper->findOneByUsername($username);
        $this->assertNull($result);
    }

    public function testFindByNameReturnsEntitiesArray()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $name = 'Nom*';
        $result = $this->mapper->findAllByName($name);
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
    }
    
    public function testFindByNameTriesDeactivatedBranchAndReturnsEntitiesArray()
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(TestAssetPeople::$data1, TestAssetPeople::$data2), array(TestAssetPeople::$dataDeactivated)));
        
        $name = 'Nom*';
        $result = $this->mapper->findAllByName($name, null, null, true);
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
    }
    
    public function testFindByNameReturnsEntitiesArrayUsingSpecifiedKey()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $name = 'Nom*';
        $result = $this->mapper->findAllByName($name, 'mail');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
        $this->assertArrayHasKey(TestAssetPeople::$data1['mail'][0], $result);
        $this->assertArrayHasKey(TestAssetPeople::$data2['mail'][0], $result);
    }
    
    public function testFindByNameAppendsSpecifiedFilter()
    {
        $name = 'Nom*';
        $filterUtilisateur = '(sexe=M)';
        
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->with(new PHPUnit_Framework_Constraint_StringContains($filterUtilisateur) /* peu importe les arguments suivants */)
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $this->mapper->findAllByName($name, null, $filterUtilisateur);
    }

    public function testFindByNameOrUsernameReturnsEntitiesArray()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $name = 'gauthier';
        $result = $this->mapper->findAllByNameOrUsername($name);
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
    }
    
    public function testFindByNameOrUsernameTriesDeactivatedBranchAndReturnsEntitiesArray()
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(TestAssetPeople::$data1, TestAssetPeople::$data2), array(TestAssetPeople::$dataDeactivated)));
        
        $name = 'gauthier';
        $result = $this->mapper->findAllByNameOrUsername($name, null, null, true);
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
    }
    
    public function testFindByNameOrUsernameReturnsEntitiesArrayUsingSpecifiedKey()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $name = 'gauthier';
        $result = $this->mapper->findAllByName($name, 'mail');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
        $this->assertArrayHasKey(TestAssetPeople::$data1['mail'][0], $result);
        $this->assertArrayHasKey(TestAssetPeople::$data2['mail'][0], $result);
    }
    
    public function testFindByNameOrUsernameAppendsSpecifiedFilter()
    {
        $name = 'gauthier';
        $filterUtilisateur = '(sexe=M)';
        
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->with(new PHPUnit_Framework_Constraint_StringContains($filterUtilisateur) /* peu importe les arguments suivants */)
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $this->mapper->findAllByNameOrUsername($name, null, '(sexe=M)');
    }
    
    public function provideStructureCode()
    {
        return array(
            'format-harpege' => array('C68'),
            'format-supann'  => array('HS_C68'),
        );
    }
    
    /**
     * @dataProvider provideStructureCode
     * @param string $code
     */
    public function testFindAllByAffectationSpecifiedAsStringReturnsEntitiesArray($code)
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(TestAssetStructure::$data1), array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $result = $this->mapper->findAllByAffectation($code);
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
    }
    
    /**
     * @expectedException Exception
     */
    public function testFindAllByAffectationSpecifiedAsStringThrowsExceptionWhenStructureNotFound()
    {
        $this->ldap->expects($this->exactly(2))
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $code = 'XXXXX';
        $result = $this->mapper->findAllByAffectation($code);
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
    }
    
    public function testFindAllByAffectationSpecifiedAsEntityReturnsEntitiesArray()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1)));
        
        $object = new EntityStructure(TestAssetEntityStructure::$data1);
        $result = $this->mapper->findAllByAffectation($object);
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
    }
    
    public function testFindAllByAffectationSpecifiedAsEntityTriesDeactivatedBranchAndReturnsEntitiesArray()
    {
        $this->ldap->expects($this->any())
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(TestAssetPeople::$data1, TestAssetPeople::$data2), array(TestAssetPeople::$dataDeactivated)));
        
        $object = new EntityStructure(TestAssetEntityStructure::$data1);
        $result = $this->mapper->findAllByAffectation($object, null, array(), true);
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
    }
    
    public function testFindAllByAffectationReturnsEntitiesArrayUsingSpecifiedKey()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $object = new EntityStructure(TestAssetEntityStructure::$data1);
        $result = $this->mapper->findAllByAffectation($object, 'mail');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
        $this->assertArrayHasKey(TestAssetPeople::$data1['mail'][0], $result);
        $this->assertArrayHasKey(TestAssetPeople::$data2['mail'][0], $result);
    }
    
    public function testFindAllByAffectationCompletesAttributesList()
    {
        $anything = new PHPUnit_Framework_Constraint_IsAnything();
        $expectedAttributes = array('dn', 'mail', 'cn');
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->with($anything, $anything, $anything, $expectedAttributes /* peu importe les arguments suivants */)
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $object = new EntityStructure(TestAssetEntityStructure::$data1);
        $attributes = array('dn');
        $this->mapper->findAllByAffectation($object, 'mail', $attributes);
    }
    
    public function provideGroup()
    {
        return array(
            'string-group' => array('cn=support_info,ou=groups,dc=unicaen,dc=fr'), 
            'entity-group' => array(new EntityGroup(TestAssetEntityGroup::$data1)), 
        );
    }
    
    /**
     * @dataProvider provideGroup
     * @param string|EntityGroup $group
     */
    public function testFindAllByMembershipReturnsEntitiesArray($group)
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $result = $this->mapper->findAllByMembership($group);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
        $this->assertContainsOnly('string', array_keys($result));
    }
    
    /**
     * @dataProvider provideGroup
     * @param string|EntityGroup $group
     */
    public function testFindAllByMembershipReturnsEmptyArrayWhenNoEntryFound($group)
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $result = $this->mapper->findAllByMembership($group);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }
    
    public function provideGroupAndStructure()
    {
        $stringGroup     = 'cn=support_info,ou=groups,dc=unicaen,dc=fr';
        $stringStructure = 'supannCodeEntite=HS_C68,ou=structures,dc=unicaen,dc=fr';
        $entityGroup     = new EntityGroup(TestAssetEntityGroup::$data1);
        $entityStructure = new EntityStructure(TestAssetEntityStructure::$data1);
        return array(
            'string-group_string-structure' => array($stringGroup, $stringStructure),
            'string-group_entity-structure' => array($stringGroup, $entityStructure),
            'entity-group_string-structure' => array($entityGroup, $stringStructure),
            'entity-group_entity-structure' => array($entityGroup, $entityStructure),
        );
    }
    
    /**
     * @dataProvider provideGroupAndStructure
     * @param string|EntityGroup $group
     * @param string|EntityStructure $structure
     */
    public function testFindAllByMembershipForStructureReturnsEntitiesArray($group, $structure)
    {
        if (is_object($structure)) {
            $this->ldap->expects($this->once())
                       ->method('searchEntries')
                       ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        }
        else {
            $this->ldap->expects($this->exactly(2))
                       ->method('searchEntries')
                       ->will($this->onConsecutiveCalls(array(TestAssetStructure::$data1), array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        }
        
        $result = $this->mapper->findAllByMembership($group, $structure);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
        $this->assertContainsOnly('string', array_keys($result));
    }
    
    /**
     * @expectedException Exception
     */
    public function testFindAllByMembershipForStructureThrowsExceptionWhenStructureNotFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $group = 'peu-importe';
        $structure = 'peu-importe';
        $this->mapper->findAllByMembership($group, $structure);
    }
    
    public function testFindAllByMembershipForStructureAppendsAffectationFilter()
    {
        $group = new EntityGroup(TestAssetEntityGroup::$data1);
        $structure = new EntityStructure(TestAssetEntityStructure::$data1);
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->with(new PHPUnit_Framework_Constraint_StringContains(sprintf('(eduPersonOrgUnitDN=%s)', $structure->getDn())))
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $this->mapper->findAllByMembership($group, $structure);
    }
    
    public function testFindAllByMembershipForStructureRecursivelyClimbsUpHierarchy()
    {
        $group = new EntityGroup(TestAssetEntityGroup::$data1);
        $structure = 'peu-importe'; // structure de niveau 2
        $this->ldap->expects($this->exactly(4))
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(
                           array(TestAssetStructure::$data2), // résultat de la recherche de la structure de niveau 2
                           array(), // aucun membre trouvé affecté à la structure de niveau 2
                           array(TestAssetStructure::$data1), // résultat de la recherche de la structure mère (niveau 1)
                           array(TestAssetPeople::$data1, TestAssetPeople::$data2))); // 2 membres trouvés affectés à la structure mère
        
        $result = $this->mapper->findAllByMembership($group, $structure, true);
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
    }
    
    public function testFindAllByRoleReturnsEntitiesArray()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $role = 'T84';
        $result = $this->mapper->findAllByRole($role);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
        $this->assertContainsOnly('string', array_keys($result));
    }
    
    public function testFindAllByRoleReturnsEmptyArrayWhenNoEntryFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $role = 'T84';
        $result = $this->mapper->findAllByRole($role);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }
    
    public function provideStructure()
    {
        return array(
            'string-structure'  => array('supannCodeEntite=HS_C68,ou=structures,dc=unicaen,dc=fr'),
            'entity-structure'  => array(new EntityStructure(TestAssetEntityStructure::$data1)),
            'string-structures' => array('supannCodeEntite=HS_C68,ou=structures,dc=unicaen,dc=fr', 'supannCodeEntite=HS_G72,ou=structures,dc=unicaen,dc=fr'),
            'entity-structures' => array(new EntityStructure(TestAssetEntityStructure::$data1), new EntityStructure(TestAssetEntityStructure::$data2)),
        );
    }
    
    /**
     * @dataProvider provideStructure
     * @param string|EntityStructure $structure
     */
    public function testFindAllByRoleForStructureReturnsEntitiesArray($structure)
    {
        if (is_object($structure)) {
            $this->ldap->expects($this->once())
                       ->method('searchEntries')
                       ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        }
        else {
            $this->ldap->expects($this->exactly(2))
                       ->method('searchEntries')
                       ->will($this->onConsecutiveCalls(array(TestAssetStructure::$data1), array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        }
        
        $role = 'T84';
        $result = $this->mapper->findAllByRole($role, $structure);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
        $this->assertContainsOnly('string', array_keys($result));
    }
    
    /**
     * @expectedException Exception
     */
    public function testFindAllByRoleForStructureThrowsExceptionWhenStructureNotFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(array(), array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $role = 'peu-importe';
        $structure = 'peu-importe';
        $this->mapper->findAllByRole($role, $structure);
    }
    
    public function testFindAllByRoleForStructureAppendsAffectationFilter()
    {
        $role = 'T84';
        $structure = new EntityStructure(TestAssetEntityStructure::$data1);
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->with(new PHPUnit_Framework_Constraint_StringContains(sprintf('(supannRoleEntite=[role={SUPANN}T84][type={SUPANN}*][code=%s]*)', $structure->getSupannCodeEntite())))
                   ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        
        $this->mapper->findAllByRole($role, $structure);
    }
    
    public function testFindAllByRoleForStructureRecursivelyClimbsUpHierarchy()
    {
        $structure = 'peu-importe'; // structure de niveau 2
        $this->ldap->expects($this->exactly(4))
                   ->method('searchEntries')
                   ->will($this->onConsecutiveCalls(
                           array(TestAssetStructure::$data2), // résultat de la recherche de la structure de niveau 2
                           array(), // aucun membre trouvé affecté à la structure de niveau 2
                           array(TestAssetStructure::$data1), // résultat de la recherche de la structure mère (niveau 1)
                           array(TestAssetPeople::$data1, TestAssetPeople::$data2))); // 2 membres trouvés affectés à la structure mère
        
        $role = 'T84';
        $result = $this->mapper->findAllByRole($role, $structure, true);
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
    }
    
    /**
     * @dataProvider provideStructure
     * @param string|EntityStructure $structure
     */
    public function testFindAllTeachersByStructureReturnsEntitiesArray($structure)
    {
        if (is_object($structure)) {
            $this->ldap->expects($this->once())
                       ->method('searchEntries')
                       ->will($this->returnValue(array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        }
        else {
            $this->ldap->expects($this->exactly(2))
                       ->method('searchEntries')
                       ->will($this->onConsecutiveCalls(array(TestAssetStructure::$data1), array(TestAssetPeople::$data1, TestAssetPeople::$data2)));
        }
        
        $result = $this->mapper->findAllTeachersByStructure($structure);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf($this->entityClassName, $result);
        $this->assertContainsOnly('string', array_keys($result));
    }
    
    public function testFindAllTeachersByStructureReturnsEmptyArrayWhenNoEntryFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $structure = new EntityStructure(TestAssetEntityStructure::$data1);
        $result = $this->mapper->findAllTeachersByStructure($structure);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }
    
    /**
     * @expectedException Exception
     */
    public function testFindAllTeachersByStructureThrowsExceptionWhenStructureNotFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $structure = 'peu-importe';
        $this->mapper->findAllTeachersByStructure($structure);
    }
    
    /**
     * @dataProvider provideStructure
     * @param string|EntityStructure $structure
     */
    public function testCreatingFilterForAffectation($structure)
    {
        $filter = People::createFilterForAffectation($structure);
        $this->assertInternalType('string', $filter);
        $this->assertNotEmpty($filter);
    }
}