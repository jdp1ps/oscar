<?php
namespace UnicaenAppTest\Entity\Ldap;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_Error;
use UnicaenApp\Entity\Ldap\People;
use UnicaenAppTest\Entity\Ldap\TestAsset\People as TestAssetPeople;
use UnicaenAppTest\Entity\Ldap\TestAsset\Structure as TestAssetStructure;
use UnicaenAppTest\Entity\Ldap\TestAsset\Group as TestAssetGroup;

/**
 * Tests concernant la classe d'entité LDAP des individus.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class PeopleTest extends PHPUnit_Framework_TestCase
{
    public function provideConstructorValidData()
    {
        return array(
            'a' => array(TestAssetPeople::$data1),
            'b' => array(TestAssetPeople::$data2),
        );
    }
    
    public function provideConstructorValidDataDeactivated()
    {
        return array(
            'a' => array(TestAssetPeople::$dataDeactivated),
        );
    }
    
    public function provideConstructorInvalidData()
    {
        $data = array( // NB: pas de 'dn'
            'uid'              => 'p00003367',
            'cn'               => 'Hochon Paule',
            'supannaliaslogin' => 'hochon',
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
        $entity = new People($data);
        $this->assertInstanceOf('\UnicaenApp\Entity\Ldap\People', $entity);
    }
    
    /**
     * @dataProvider provideConstructorValidDataDeactivated
     */
    public function testConstructWithValidDataDeactivated($data)
    {
        $entity = new People($data);
        $this->assertTrue($entity->estDesactive());
    }
    
    /**
     * @dataProvider provideConstructorInvalidData
     * @expectedException \InvalidArgumentException
     */
    public function testConstructWithInvalidData($data)
    {
        new People($data);
    }
    
    public function provideSupannRoleEntite()
    {
        return array(
            array("[role={SUPANN}D30][type={SUPANN}S231][code=HS_C68][libelle=Directeur]",  true, "D30", "S231", "HS_C68",  "Directeur"),
            array("[role={SUPANN}D30][type={SUPANN}S231][code=HS_C68]",  false, null, null, null,  null),
        );
    }
    
    /**
     * 
     * @param string $string
     * @param bool $expected
     * @param string $expectedRole
     * @param string $expectedTypeStructure
     * @param string $expectedCodeStructure
     * @param string $expectedLibelleRole
     * @dataProvider provideSupannRoleEntite
     */
    public function testCanCheckSupannRoleEntite(
            $string, $expected, $expectedRole, $expectedTypeStructure, $expectedCodeStructure, $expectedLibelleRole)
    {
        $check = People::isSupannRoleEntite($string, $role, $typeStructure, $codeStructure, $libelleRole);
        $this->assertEquals($expected, $check);
        $this->assertEquals($expectedRole, $role);
        $this->assertEquals($expectedTypeStructure, $typeStructure);
        $this->assertEquals($expectedCodeStructure, $codeStructure);
        $this->assertEquals($expectedLibelleRole, $libelleRole);
    }
    
    /**
     * @depends testConstructWithValidData
     */
    public function testGetters()
    {
        $entity = new People(TestAssetPeople::$data1);
        $this->assertEquals(TestAssetPeople::$data1['cn'], $entity->getCn());
        $this->assertEquals(TestAssetPeople::$data1['datedenaissance'], $entity->getDateDeNaissance());
        $this->assertEquals(TestAssetPeople::$data1['displayname'], $entity->getDisplayName());
        $this->assertEquals(TestAssetPeople::$data1['dn'], $entity->getDn());
        $this->assertEquals(TestAssetPeople::$data1['givenname'], $entity->getGivenName());
        $this->assertEquals(TestAssetPeople::$data1['mail'], $entity->getMail());
        $this->assertEquals(TestAssetPeople::$data1['postaladdress'], $entity->getPostalAddress());
        $this->assertEquals(TestAssetPeople::$data1['sexe'], $entity->getSexe());
        $this->assertEquals(TestAssetPeople::$data1['sn'], $entity->getSn(false));
        $this->assertEquals(TestAssetPeople::$data1['sn'], $entity->getSn(true));
        $this->assertEquals(TestAssetPeople::$data1['sn'], $entity->getNomUsuel());
        $this->assertEquals(TestAssetPeople::$data1['sn'], $entity->getNomPatronymique());
        $this->assertEquals(TestAssetPeople::$data1['edupersonorgunitdn'], $entity->getEduPersonOrgUnitDN());
        $this->assertEquals(TestAssetPeople::$data1['edupersonprimaryorgunitdn'], $entity->getEduPersonPrimaryOrgUnitDN());
        $this->assertEquals(TestAssetPeople::$data1['ucbnstructurerecherche'], $entity->getUcbnStructureRecherche());
        $this->assertEquals(TestAssetPeople::$data1['supannaliaslogin'], $entity->getSupannAliasLogin());
        $this->assertEquals(TestAssetPeople::$data1['supanncivilite'], $entity->getSupannCivilite());
        $this->assertEquals(TestAssetPeople::$data1['supannempid'], $entity->getSupannEmpId());
        $this->assertEquals(TestAssetPeople::$data1['telephonenumber'], $entity->getTelephoneNumber());
        $this->assertEquals(TestAssetPeople::$data1['ucbnsousstructure'], $entity->getUcbnSousStructure());
        $this->assertEquals(TestAssetPeople::$data1['ucbnfonctionstructurelle'], $entity->getUcbnFonctionStructurelle());
        $this->assertEquals(TestAssetPeople::$data1['supannroleentite'], $entity->getSupannRoleEntite());
        $this->assertEquals(TestAssetPeople::$data1['ucbnsitelocalisation'], $entity->getUcbnSiteLocalisation());
        $this->assertEquals(TestAssetPeople::$data1['ucbnstatus'], $entity->getUcbnStatus());
        $this->assertEquals(TestAssetPeople::$data1['uid'], $entity->getUid());
        $this->assertEquals(TestAssetPeople::$data1['uidnumber'], $entity->getUidNumber());
        $this->assertEquals(TestAssetPeople::$data1['memberof'], $entity->getMemberOf());
        $this->assertFalse($entity->estDesactive());
        $this->assertFalse($entity->estEtudiant());
        
        $entity = new People(TestAssetPeople::$data2);
        $this->assertEquals(TestAssetPeople::$data2['cn'], $entity->getCn());
        $this->assertEquals(TestAssetPeople::$data2['datedenaissance'], $entity->getDateDeNaissance());
        $this->assertEquals(TestAssetPeople::$data2['displayname'], $entity->getDisplayName());
        $this->assertEquals(TestAssetPeople::$data2['dn'], $entity->getDn());
        $this->assertEquals(TestAssetPeople::$data2['givenname'], $entity->getGivenName());
        $this->assertEquals(TestAssetPeople::$data2['mail'], $entity->getMail());
        $this->assertEquals(TestAssetPeople::$data2['postaladdress'], $entity->getPostalAddress());
        $this->assertEquals(TestAssetPeople::$data2['sexe'], $entity->getSexe());
        $this->assertInternalType('array', $entity->getSn(false));
        $this->assertEquals(TestAssetPeople::$data2['sn'], $entity->getSn(false));
        $this->assertEquals(TestAssetPeople::$data2['sn'][0], $entity->getSn(true));
        $this->assertEquals(TestAssetPeople::$data2['sn'][0], $entity->getNomUsuel());
        $this->assertEquals(TestAssetPeople::$data2['sn'][1], $entity->getNomPatronymique());
        $this->assertEquals(TestAssetPeople::$data2['edupersonorgunitdn'], $entity->getEduPersonOrgUnitDN());
        $this->assertEquals(TestAssetPeople::$data2['edupersonprimaryorgunitdn'], $entity->getEduPersonPrimaryOrgUnitDN());
        $this->assertEquals(TestAssetPeople::$data2['ucbnstructurerecherche'], $entity->getUcbnStructureRecherche());
        $this->assertEquals(TestAssetPeople::$data2['supannaliaslogin'], $entity->getSupannAliasLogin());
        $this->assertEquals(TestAssetPeople::$data2['supanncivilite'], $entity->getSupannCivilite());
        $this->assertEquals(TestAssetPeople::$data2['supannempid'], $entity->getSupannEmpId());
        $this->assertEquals(TestAssetPeople::$data2['telephonenumber'], $entity->getTelephoneNumber());
        $this->assertEquals(TestAssetPeople::$data2['ucbnsousstructure'], $entity->getUcbnSousStructure());
        $this->assertEquals(TestAssetPeople::$data2['ucbnfonctionstructurelle'], $entity->getUcbnFonctionStructurelle());
        $this->assertEquals(TestAssetPeople::$data2['supannroleentite'], $entity->getSupannRoleEntite());
        $this->assertEquals(TestAssetPeople::$data2['ucbnsitelocalisation'], $entity->getUcbnSiteLocalisation());
        $this->assertEquals(TestAssetPeople::$data2['ucbnstatus'], $entity->getUcbnStatus());
        $this->assertEquals(TestAssetPeople::$data2['uid'], $entity->getUid());
        $this->assertEquals(TestAssetPeople::$data2['uidnumber'], $entity->getUidNumber());
        $this->assertEquals(TestAssetPeople::$data2['memberof'], current($entity->getMemberOf()));
        $this->assertFalse($entity->estDesactive());
        $this->assertTrue($entity->estEtudiant());
    }
    
    /**
     * @expectedException \BadMethodCallException
     */
    public function testGetSuppannAffectationThrowsException()
    {
        $entity = new People(TestAssetPeople::$data1);
        $entity->getSupannAffectation();
    }
    
    public function testToStringReturnsString()
    {
        $entity = new People(TestAssetPeople::$data1);
        try {
            $toString = "" . $entity;
        } 
        catch (PHPUnit_Framework_Error $e) {
            $this->fail($e->getMessage());
        }
        $this->assertNotEmpty($toString);
    }
    
    public function testGettingNomCompletCanReturnEmptyString()
    {
        TestAssetPeople::$data1['sn'] = null;
        $entity = new People(TestAssetPeople::$data1);
        $this->assertEquals('', $entity->getSn());
        $this->assertEquals('', $entity->getNomComplet());
    }
    
//    public function provideGetInfosComplParams()
//    {
//        return array(
//            'a' => array(false, false, false),
//            'b' => array(false, false, true),
//            'c' => array(false, true, false),
//            'd' => array(false, true, true),
//            'e' => array(true, false, false),
//            'f' => array(true, false, true),
//            'g' => array(true, true, false),
//            'h' => array(true, true, true),
//        );
//    }
//    
//    /**
//     * @dataProvider provideGetInfosComplParams
//     */
//    public function testGetInfosCompl($affectations, $mail, $login)
//    {
//        $entity = new People(TestAsset::$data1);
//        $this->assertInternalType('string', $entity->getInfosCompl($affectations, $mail, $login));
//    }
//    
//    /**
//     * @depends      testGetInfosCompl
//     * @dataProvider provideGetInfosComplParams
//     */
//    public function testGetInfosComplDeactivated($affectations, $mail, $login)
//    {
//        $entity = new People(TestAsset::$dataDeactivated);
//        $this->assertContains('Compte DÉSACTIVÉ', $entity->getInfosCompl($affectations, $mail, $login));
//    }
    
    public function testGetAffectationsAdminChemins()
    {
        $mapper = $this->getMock('\UnicaenApp\Mapper\Ldap\Structure');
        
        $entity = new People(TestAssetPeople::$data1);
        
        $structure1 = new \UnicaenApp\Entity\Ldap\Structure(TestAssetStructure::$data1); // 1e affectation de test
        $structure2 = new \UnicaenApp\Entity\Ldap\Structure(TestAssetStructure::$data2); // 2e affectation de test
        
        $mapper->expects($this->any())
                ->method('findOneByDn')
                ->will($this->onConsecutiveCalls($structure1, $structure2));
        $mapper->expects($this->any())
                ->method('findOnePathByCodeStructure')
                ->will($this->onConsecutiveCalls($path1 = "Université > DSI", $path2 = "Université > UFR"));
        
        $affs = $entity->getAffectationsAdmin($mapper);
        $this->assertInternalType('array', $affs);
        $this->assertCount(2, $affs); // car il y a 2 affectations de test
        $this->assertContainsOnly('string', $affs, true);
        $this->assertContainsOnly('string', array_keys($affs), true);
        
        $keys = array($structure1->getCStructure(), $structure2->getCStructure());
        $vals = array($path1, $path2);
        $this->assertEquals(array_combine($keys, $vals), $affs);
    }
    
    public function testGetAffectationsAdmin()
    {
        $mapper = $this->getMock('\UnicaenApp\Mapper\Ldap\Structure');
        
        $entity = new People(TestAssetPeople::$data1);
        
        $structure1 = new \UnicaenApp\Entity\Ldap\Structure(TestAssetStructure::$data1); // 1e affectation de test
        $structure2 = new \UnicaenApp\Entity\Ldap\Structure(TestAssetStructure::$data2); // 2e affectation de test
        
        $mapper->expects($this->any())
                ->method('findOneByDn')
                ->will($this->onConsecutiveCalls($structure1, $structure2));
        $mapper->expects($this->never())
                ->method('findOnePathByCodeStructure');
        
        $affs = $entity->getAffectationsAdmin($mapper, false, false);
        $this->assertInternalType('array', $affs);
        $this->assertCount(2, $affs); // car il y a 2 affectations de test
        $this->assertContainsOnly('string', $affs, true);
        $this->assertContainsOnly('string', array_keys($affs), true);
        
        $keys = array($structure1->getCStructure(), $structure2->getCStructure());
        $vals = array($structure1->getDn(), $structure2->getDn());
        $this->assertEquals(array_combine($keys, $vals), $affs);
    }
    
    public function testGetAffectationsAdminPrincipaleChemin()
    {
        $mapper = $this->getMock('\UnicaenApp\Mapper\Ldap\Structure');
        
        $entity = new People(TestAssetPeople::$data1);
        
        $structure1 = new \UnicaenApp\Entity\Ldap\Structure(TestAssetStructure::$data1); // affectation de test
        
        $mapper->expects($this->any())
                ->method('findOneByDn')
                ->will($this->onConsecutiveCalls($structure1));
        $mapper->expects($this->any())
                ->method('findOnePathByCodeStructure')
                ->will($this->onConsecutiveCalls($path1 = "Université > DSI"));
        
        $affs = $entity->getAffectationsAdmin($mapper, true);
        $this->assertInternalType('array', $affs);
        $this->assertCount(1, $affs); // car il y a 1 seule affectation de test
        $this->assertContainsOnly('string', $affs, true);
        $this->assertContainsOnly('string', array_keys($affs), true);
        
        $this->assertEquals(array($structure1->getCStructure() => $path1), $affs);
    }
    
    public function testGetAffectationsAdminPrincipale()
    {
        $mapper = $this->getMock('\UnicaenApp\Mapper\Ldap\Structure');
        
        $entity = new People(TestAssetPeople::$data1);
        
        $structure1 = new \UnicaenApp\Entity\Ldap\Structure(TestAssetStructure::$data1); // affectation de test
        
        $mapper->expects($this->any())
                ->method('findOneByDn')
                ->will($this->onConsecutiveCalls($structure1));
        $mapper->expects($this->never())
                ->method('findOnePathByCodeStructure');
        
        $affs = $entity->getAffectationsAdmin($mapper, true, false);
        $this->assertInternalType('array', $affs);
        $this->assertCount(1, $affs); // car il y a 1 seule affectation de test
        $this->assertContainsOnly('string', $affs, true);
        $this->assertContainsOnly('string', array_keys($affs), true);
        
        $this->assertEquals(array($structure1->getCStructure() => $structure1->getDn()), $affs);
    }
    
    public function testGetAffectationsRecherche()
    {
        $structure1 = new \UnicaenApp\Entity\Ldap\Structure(TestAssetStructure::$data1); // affectation de test
        
        $mapper = $this->getMock('UnicaenApp\Mapper\Ldap\Structure');
        $mapper->expects($this->once())
                ->method('findOneByCodeStructure')
                ->will($this->returnValue($structure1));
        $mapper->expects($this->once())
                ->method('findOnePathByCodeStructure')
                ->will($this->returnValue('Chemin > Complet > Structure'));
        
        $entity = new People(TestAssetPeople::$data1);
        $affs = $entity->getAffectationsRecherche($mapper);
        $this->assertInternalType('array', $affs);
        $this->assertCount(1, $affs);
        $this->assertContainsOnly('string', $affs, true);
        $this->assertContainsOnly('string', array_keys($affs), true);
    }
    
    public function testGetFonctionsStructurelles()
    {
        $structure1 = new \UnicaenApp\Entity\Ldap\Structure(TestAssetStructure::$data1); // affectation de test
        
        $mapper = $this->getMock('UnicaenApp\Mapper\Ldap\Structure');
        $mapper->expects($this->exactly(2))
                ->method('findOneByCodeEntite')
                ->will($this->returnValue($structure1));
        $mapper->expects($this->exactly(2))
                ->method('findOnePathByCodeStructure')
                ->will($this->returnValue('Chemin > Complet > Structure'));
        
        $entity = new People(TestAssetPeople::$data1);
        $affs = $entity->getFonctionsStructurelles($mapper);
        $this->assertInternalType('array', $affs);
        $this->assertCount(2, $affs); // 2 fonctions structurelles
        $this->assertContainsOnly('string', $affs, true);
        $this->assertContainsOnly('string', array_keys($affs), true);
    }
    
    public function testGetRoles()
    {
        $entity = new People(TestAssetPeople::$data1);
        $roles = $entity->getSupannRolesEntite();
        $this->assertInternalType('array', $roles);
        $this->assertCount(2, $roles);
        $this->assertArrayHasKey(0, $roles);
        $this->assertArrayHasKey(1, $roles);
        $this->assertContainsOnly('string', $roles, true);
    }
    
    public function testGetRolesNone()
    {
        $entity = new People(TestAssetPeople::$data2);
        $roles = $entity->getSupannRolesEntite();
        $this->assertInternalType('array', $roles);
        $this->assertEmpty($roles);
    }
    
    public function provideMatchingRoleFilter()
    {
        return array(
            'a' => array('D30,S231,HS_C68'),
            'b' => array('D30,S302,HS_C681'),
        );
    }
    
    public function provideNonMatchingRoleFilter()
    {
        return array(
            'a' => array('D30,S302,ZZZZZZZ'),
            'b' => array('D30,YYYY,HS_C681'),
            'c' => array('XXX,S302,HS_C681'),
        );
    }
    
    /**
     * @depends testGetRoles
     * @dataProvider provideMatchingRoleFilter
     */
    public function testGetRolesWithMatchingRoleFilter($filter)
    { 
        $entity = new People(TestAssetPeople::$data1);
        $roles = $entity->getSupannRolesEntite($filter);
        $this->assertCount(1, $roles);
    }
    
    /**
     * @depends testGetRoles
     * @dataProvider provideNonMatchingRoleFilter
     */
    public function testGetRolesWithNonMatchingRoleFilter($filter)
    { 
        $entity = new People(TestAssetPeople::$data1);
        $roles = $entity->getSupannRolesEntite($filter);
        $this->assertEmpty($roles);
    }
    
    /**
     * @depends testGetRoles
     */
    public function testGetRolesToArray()
    {
        $entity = new People(TestAssetPeople::$data1);
        $roles = $entity->getSupannRolesEntiteToArray();
        $this->assertInternalType('array', $roles);
        $this->assertCount(2, $roles);
        $this->assertArrayHasKey(0, $roles);
        $this->assertArrayHasKey(1, $roles);
        $this->assertContainsOnly('array', $roles, true);
        foreach ($roles as $role) {
            $this->assertCount(4, $role);
            $this->assertContainsOnly('string', $role, true);
            $this->assertArrayHasKey('role', $role);
            $this->assertArrayHasKey('type', $role);
            $this->assertArrayHasKey('code', $role);
            $this->assertArrayHasKey('libelle', $role);
        }
    }
    
    /**
     * @depends testGetRolesNone
     */
    public function testGetRolesToArrayNone()
    {
        $entity = new People(TestAssetPeople::$data2);
        $roles = $entity->getSupannRolesEntiteToArray();
        $this->assertInternalType('array', $roles);
        $this->assertEmpty($roles);
    }
    
    /**
     * @depends testGetRolesToArray
     * @depends testGetRolesWithMatchingRoleFilter
     * @dataProvider provideMatchingRoleFilter
     */
    public function testGetRolesToArrayWithMatchingRoleFilter($filter)
    {
        $entity = new People(TestAssetPeople::$data1);
        $roles = $entity->getSupannRolesEntiteToArray($filter);
        $this->assertInternalType('array', $roles);
        $this->assertCount(1, $roles);
    }
    
    /**
     * @depends testGetRolesToArray
     * @depends testGetRolesWithNonMatchingRoleFilter
     * @dataProvider provideNonMatchingRoleFilter
     */
    public function testGetRolesToArrayWithNonMatchingRoleFilter($filter)
    {
        $entity = new People(TestAssetPeople::$data1);
        $roles = $entity->getSupannRolesEntiteToArray($filter);
        $this->assertEmpty($roles);
    }
    
    /**
     * @depends testGetRolesToArray
     */
    public function testGetRolesToArrayCondensed()
    {
        $entity = new People(TestAssetPeople::$data1);
        $roles = $entity->getSupannRolesEntiteToArray(null, true);
        $this->assertInternalType('array', $roles);
        $this->assertCount(2, $roles);
        $this->assertContainsOnly('string', array_keys($roles), true);
        $this->assertContainsOnly('array', $roles, true);
        foreach ($roles as $role) {
            $this->assertCount(1, $role);
            $this->assertContainsOnly('string', array_keys($role), true);
            $this->assertContainsOnly('string', $role, true);
        }
    }
    
    /**
     * @depends testGetRolesToArrayCondensed
     * @dataProvider provideMatchingRoleFilter
     */
    public function testGetRolesToArrayCondensedWithMatchingRoleFilter($filter)
    {
        $entity = new People(TestAssetPeople::$data1);
        $roles = $entity->getSupannRolesEntiteToArray($filter, true);
        $this->assertInternalType('array', $roles);
        $this->assertCount(1, $roles);
    }
    
    /**
     * @depends testGetRolesToArrayCondensed
     * @dataProvider provideNonMatchingRoleFilter
     */
    public function testGetRolesToArrayCondensedWithNonMatchingRoleFilter($filter)
    {
        $entity = new People(TestAssetPeople::$data1);
        $roles = $entity->getSupannRolesEntiteToArray($filter, true);
        $this->assertEmpty($roles);
    }
    
    public function provideEntitiesToCompare()
    {
        $entity1 = new People(TestAssetPeople::$data1);
        $entity2 = new People(array_merge(TestAssetPeople::$data2, array('cn' => TestAssetPeople::$data1['cn']))); // même CN que $entity1
        $entity3 = new People(array_merge(TestAssetPeople::$data1, array('cn' => "Zaoui Antonio")));
        $entity4 = new People(array_merge(TestAssetPeople::$data1, array('cn' => "Aaron Joe")));
        return array(
            'a' => array($entity1, $entity2, 'assertEquals'),
            'b' => array($entity2, $entity1, 'assertEquals'),
            'c' => array($entity1, $entity3, 'assertLessThan'),
            'd' => array($entity3, $entity1, 'assertGreaterThan'),
            'e' => array($entity1, $entity4, 'assertGreaterThan'),
            'f' => array($entity4, $entity1, 'assertLessThan'),
        );
    }
    
    /**
     * @dataProvider provideEntitiesToCompare
     */
    public function testStrcasecmpLdapPeople($entity1, $entity2, $assertMethod)
    {
        $compare = People::strcasecmpLdapPeople($entity1, $entity2);
        $this->assertInternalType('int', $compare);
        $this->$assertMethod(0, $compare);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testStrcasecmpLdapPeopleWithEmptyCn()
    {
        $entity1 = new People(TestAssetPeople::$data1);
        $entity2 = new People(array_merge(TestAssetPeople::$data1, array('cn' => "")));
        People::strcasecmpLdapPeople($entity1, $entity2);
    }
    
    public function testGetMemberOfGroupWithMatchingGroupsSpecifiedReturnsMatchingGroups()
    {
        $mapper = $this->getMock('\UnicaenApp\Mapper\Ldap\Group');
        
        $group1 = new \UnicaenApp\Entity\Ldap\Group(TestAssetGroup::$data1);
        $group2 = new \UnicaenApp\Entity\Ldap\Group(TestAssetGroup::$data2);
        $group3 = new \UnicaenApp\Entity\Ldap\Group(TestAssetGroup::$data3);
        
        $entity = new People(TestAssetPeople::$data1);
        
        $mapper->expects($this->any())
                ->method('findOneByCn')
                ->will($this->onConsecutiveCalls($group1, $group2, $group3));
        
        $groups = $entity->getMemberOfGroup($mapper);
        $this->assertInternalType('array', $groups);
        $this->assertCount(3, $groups);
        $this->assertContainsOnlyInstancesOf('\UnicaenApp\Entity\Ldap\Group', $groups);
        
        $keys = array($group1->getDn(), $group2->getDn(), $group3->getDn());
        $vals = array($group1, $group2, $group3);
        $this->assertEquals(array_combine($keys, $vals), $groups);
    }
    
    public function testGetMemberOfGroupReturnsMatchingDateGroups()
    {
        $mapper = $this->getMock('\UnicaenApp\Mapper\Ldap\Group');
        
        $groupFini = new \UnicaenApp\Entity\Ldap\Group(TestAssetGroup::$data3);
        
        $mapper->expects($this->any())
                ->method('findOneByCn')
                ->will($this->returnValue($groupFini));
     
        $entity = new People(TestAssetPeople::$data1);
        $dateObs = new \DateTime(); 
        
        $groups = $entity->getMemberOfGroup($mapper, $dateObs->setDate(2012, 1, 1));
        $this->assertEquals(array($groupFini->getDn() => $groupFini), $groups);
        
        $groups = $entity->getMemberOfGroup($mapper, $dateObs->setDate(2014, 1, 1));
        $this->assertEmpty($groups);
    }

    /**
     * @expectedException \UnicaenApp\Exception\RuntimeException
     */
    public function testGetMemberOfGroupThrowsExceptionWhenNoGroupFound()
    {
        $mapper = $this->getMock('\UnicaenApp\Mapper\Ldap\Group');
        
        $mapper->expects($this->any())
                ->method('findOneByCn')
                ->will($this->returnValue(null));
        
        $entity = new People(TestAssetPeople::$data1);
        $entity->getMemberOfGroup($mapper);
    }
    
    public function testIsMemberOfWithMatchingGroupsSpecifiedReturnsTrue()
    {
        $groupDeptInfo = new \UnicaenApp\Entity\Ldap\Group(TestAssetGroup::$data2);
        
        $entity = new People(TestAssetPeople::$data1);
        
        // as object
        $is = $entity->isMemberOf($groupDeptInfo);
        $this->assertTrue($is);
        
        // as string
        $is = $entity->isMemberOf($groupDeptInfo->getDn());
        $this->assertTrue($is);
    }
    
    public function testIsMemberOfWithNonMatchingGroupsSpecifiedReturnsFalse()
    {
        $groupDeptInfo = new \UnicaenApp\Entity\Ldap\Group(TestAssetGroup::$data2);
        $groupRssi     = new \UnicaenApp\Entity\Ldap\Group(TestAssetGroup::$data4);
        
        $entity = new People(TestAssetPeople::$data1);
        
        // as string
        $is = $entity->isMemberOf($groupRssi->getDn());
        $this->assertFalse($is);
        // as string[]
        $is = $entity->isMemberOf(array($groupDeptInfo->getDn(), $groupRssi->getDn()));
        $this->assertFalse($is);
        
        // as object
        $is = $entity->isMemberOf($groupRssi);
        $this->assertFalse($is);
        // as object[]
        $is = $entity->isMemberOf(array($groupRssi, $groupRssi));
        $this->assertFalse($is);
    }
    
    public function testIsMemberOfWithMatchingGroupsAndMatchingDateSpecifiedReturnsTrue()
    {
        $entity = new People(TestAssetPeople::$data1);
        
        // as object
        $groupDeptInfo = new \UnicaenApp\Entity\Ldap\Group(TestAssetGroup::$data2);
        $is = $entity->isMemberOf($groupDeptInfo, new \DateTime());
        $this->assertTrue($is);
        
        // as string
        $mapper = $this->getMock('\UnicaenApp\Mapper\Ldap\Group');
        $groupFini = new \UnicaenApp\Entity\Ldap\Group(TestAssetGroup::$data3);
        $mapper->expects($this->any())
                ->method('findOneByCn')
                ->will($this->returnValue($groupFini));
        $dateObs = new \DateTime();
        $is = $entity->isMemberOf($groupFini->getDn(), $dateObs->setDate(2012, 1, 1), $mapper);
        $this->assertTrue($is);
    }
    
    public function testIsMemberOfWithMatchingGroupsButNonMatchingDateSpecifiedReturnsFalse()
    {
        $entity = new People(TestAssetPeople::$data1);
        
        $groupFini = new \UnicaenApp\Entity\Ldap\Group(TestAssetGroup::$data3);
        $dateObs = new \DateTime();
        
        // as object
        $is = $entity->isMemberOf($groupFini, $dateObs);
        $this->assertFalse($is);
        
        // as string
        $mapper = $this->getMock('\UnicaenApp\Mapper\Ldap\Group');
        $mapper->expects($this->any())
                ->method('findOneByCn')
                ->will($this->returnValue($groupFini));
        $is = $entity->isMemberOf($groupFini->getDn(), $dateObs, $mapper);
        $this->assertFalse($is);
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\LogicException
     */
    public function testIsMemberOfWithDateButNoMapperSpecifiedThrowsException()
    {
        $groupConsultPandemie = 'cn=consult_pandemie,ou=groups,dc=unicaen,dc=fr';
        
        $entity = new People(TestAssetPeople::$data2);
        
        // as string
        $entity->isMemberOf($groupConsultPandemie, new \DateTime()); // no mapper specified
    }
}
