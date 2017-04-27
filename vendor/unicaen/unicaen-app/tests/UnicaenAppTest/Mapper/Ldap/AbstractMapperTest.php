<?php
namespace UnicaenAppTest\Mapper\Ldap;

use UnicaenApp\Mapper\Ldap\People;
use UnicaenApp\Mapper\Ldap\AbstractMapper;

/**
 * Classe de test de la classe mère des mappers LDAP.
 *
 * @property AbstractMapper $mapper 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AbstractMapperTest extends CommonTest
{
    /**
     * @var array
     */
    protected $rawEntry = array(
        "null"                      => null,
        "emptystring"               => "",
        "emptyarray"                => array(),
        "cn"                        => array("Gauthier Bertrand"),
        "datedenaissance"           => array("19790311"),
        "displayname"               => array("Bertrand Gauthier"),
        "dn"                        => array("uid=p00021237,ou=people,dc=unicaen,dc=fr"),
        "edupersonaffiliation"      => array("staff", "employee", "member"),
        "edupersonorgunitdn"        => array("supannCodeEntite=HS_C68,ou=structures,dc=unicaen,dc=fr"),
        "edupersonprimaryorgunitdn" => array("supannCodeEntite=HS_C68,ou=structures,dc=unicaen,dc=fr"),
        "givenname"                 => array("Bertrand"),
        "mail"                      => array("bertrand.gauthier@unicaen.fr"),
        "objectclass"               => array("top", "person", "organizationalPerson", "inetOrgPerson", "eduPerson", "supannPerson", "ucbnEmp", "posixAccount", "sambaAccount", "sambaSamAccount"),
        "supannaffectation"         => array("C68;Direction du système d'information (DSI)"),
        "supanncivilite"            => array("M."),
        "supannempid"               => array("00021237"),
    );
    
    /**
     * @var array
     */
    protected $simplifiedEntry = array(
        "null"                      => null,
        "emptystring"               => "",
        "emptyarray"                => array(),
        "cn"                        => "Gauthier Bertrand",
        "datedenaissance"           => "19790311",
        "displayname"               => "Bertrand Gauthier",
        "dn"                        => "uid=p00021237,ou=people,dc=unicaen,dc=fr",
        "edupersonaffiliation"      => array("staff", "employee", "member"),
        "edupersonorgunitdn"        => "supannCodeEntite=HS_C68,ou=structures,dc=unicaen,dc=fr",
        "edupersonprimaryorgunitdn" => "supannCodeEntite=HS_C68,ou=structures,dc=unicaen,dc=fr",
        "givenname"                 => "Bertrand",
        "mail"                      => "bertrand.gauthier@unicaen.fr",
        "objectclass"               => array("top", "person", "organizationalPerson", "inetOrgPerson", "eduPerson", "supannPerson", "ucbnEmp", "posixAccount", "sambaAccount", "sambaSamAccount"),
        "supannaffectation"         => "C68;Direction du système d'information (DSI)",
        "supanncivilite"            => "M.",
        "supannempid"               => "00021237",
    );
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->mapper = $this->getMockForAbstractClass('\UnicaenApp\Mapper\Ldap\AbstractMapper');
        $this->mapper->setLdap($this->ldap);
        $this->mapper->expects($this->any())
                     ->method('getAttributes')
                     ->will($this->returnValue(array('*')));
    }
    
    public function testCanSimplifyEntry()
    {
        $simplifiedEntry = $this->mapper->simplifiedEntry($this->rawEntry);
        $this->assertSame($this->simplifiedEntry, $simplifiedEntry);
    }
    
    public function testCanSimplifyEntryIncludingAttributes()
    {
        $returnAttributes = array(
            "cn", 
            "mail",
        );
        $simplifiedEntry = $this->mapper->simplifiedEntry($this->rawEntry, $returnAttributes);
        $expected = array(
            "cn"   => "Gauthier Bertrand",
            "mail" => "bertrand.gauthier@unicaen.fr",
        );
        $this->assertEquals($expected, $simplifiedEntry);
    }
    
    public function testCanSimplifyEntryOmittingAttributes()
    {
        $omitAttributes = array(
            "null",
            "emptystring",
            "emptyarray",
            "datedenaissance",
            "displayname",
            "dn",
            "edupersonaffiliation",
            "edupersonorgunitdn",
            "edupersonprimaryorgunitdn",
            "givenname",
            "objectclass",
            "supannaffectation",
            "supanncivilite",
            "supannempid",
        );
        $simplifiedEntry = $this->mapper->simplifiedEntry($this->rawEntry, array(), $omitAttributes);
        $expected = array(
            "cn"   => "Gauthier Bertrand",
            "mail" => "bertrand.gauthier@unicaen.fr",
        );
        $this->assertEquals($expected, $simplifiedEntry);
    }

    public function testSearchSimplifiedEntryReturnsExpectedArray()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array($this->rawEntry)));
        
        $result = $this->mapper->searchSimplifiedEntry('(uid=p00021237)', People::UTILISATEURS_BASE_DN);
        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertNotInternalType('integer', key($result));
        $this->assertArrayHasKey('dn', $result);
    }
    
    /**
     * @expectedException        \UnicaenApp\Exception\RuntimeException
     * @expectedExceptionMessage Plus d'une entrée trouvée
     */
    public function testSearchSimplifiedEntryThrowsExceptionWhenMoreThanOneEntryFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(array($this->rawEntry), array($this->rawEntry))));
        
        $uidFilterPattern = '(uid=%s)';
        $existingUidPattern = 'p000212*';
        $this->mapper->searchSimplifiedEntry(sprintf($uidFilterPattern, $existingUidPattern), People::UTILISATEURS_BASE_DN);
    }
    
    public function testSearchSimplifiedEntryReturnsNullWhenNoEntryFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $result = $this->mapper->searchSimplifiedEntry('(uid=unknown_uid)', People::UTILISATEURS_BASE_DN);
        $this->assertNull($result);
    }
    
    public function testSearchSimplifiedEntriesReturnsExpectedArray()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array(array($this->rawEntry), array($this->rawEntry))));
        
        $result = $this->mapper->searchSimplifiedEntries('(uid=p000212*)', People::UTILISATEURS_BASE_DN);
        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertGreaterThan(1, count($result));
    }
    
    public function testSearchSimplifiedEntriesReturnsEmptyArrayWhenNoEntryFound()
    {
        $this->ldap->expects($this->once())
                   ->method('searchEntries')
                   ->will($this->returnValue(array()));
        
        $result = $this->mapper->searchSimplifiedEntries('(uid=unknown_uid*)', People::UTILISATEURS_BASE_DN);
        $this->assertNotNull($result);
        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);
    }
}