<?php

use Oscar\Connector\DataExtractionStrategy\LdapExtractionStrategy;
use PHPUnit\Framework\TestCase;
use Zend\Ldap\Exception\LdapException;

class ConnectorLdapPersonTest extends TestCase
{
    private array $settingsLdap = array(
        'ldap' => [
            'connection' => array(
                'default' => array(
                    'params' => array(
                        'host'                => 'ldap.test.univ-paris1.fr',
                        'port'                => 389,
                        'username'            => "user",
                        'password'            => "password",
                        'baseDn'              => "cn=oscardev,ou=admin,dc=univ-paris1,dc=fr",
                        'bindRequiresDn'      => true,
                        'accountFilterFormat' => "(&(objectClass=posixAccount)(supannAliasLogin=%s))",
                    )
                )
            ),
            'dn' => [
                'UTILISATEURS_BASE_DN'                  => 'ou=people,dc=unicaen,dc=fr',
                'UTILISATEURS_DESACTIVES_BASE_DN'       => 'ou=deactivated,dc=unicaen,dc=fr',
                'GROUPS_BASE_DN'                        => 'ou=groups,dc=unicaen,dc=fr',
                'STRUCTURES_BASE_DN'                    => 'ou=structures,dc=unicaen,dc=fr',
            ],
            'filters' => [
                'LOGIN_FILTER'                          => '(uid=%s)',
                'UTILISATEUR_STD_FILTER'                => '(|(uid=p*)(&(uid=e*)(eduPersonAffiliation=student)))',
                'CN_FILTER'                             => '(cn=%s)',
                'NAME_FILTER'                           => '(cn=%s*)',
                'UID_FILTER'                            => '(uid=%s)',
                'NO_INDIVIDU_FILTER'                    => '(supannEmpId=%08s)',
                'AFFECTATION_FILTER'                    => '(&(uid=*)(eduPersonOrgUnitDN=%s))',
                'AFFECTATION_CSTRUCT_FILTER'
                => '(&(uid=*)(|(ucbnSousStructure=%s;*)(supannAffectation=%s;*)))',
                'LOGIN_OR_NAME_FILTER'                  => '(|(supannAliasLogin=%s)(cn=%s*))',
                'MEMBERSHIP_FILTER'                     => '(memberOf=%s)',
                'AFFECTATION_ORG_UNIT_FILTER'           => '(eduPersonOrgUnitDN=%s)',
                'AFFECTATION_ORG_UNIT_PRIMARY_FILTER'   => '(eduPersonPrimaryOrgUnitDN=%s)',
                'ROLE_FILTER'
                => '(supannRoleEntite=[role={SUPANN}%s][type={SUPANN}%s][code=%s]*)',
                'PROF_STRUCTURE'
                => '(&(eduPersonAffiliation=teacher)(eduPersonOrgUnitDN=%s))',
                'FILTER_STRUCTURE_DN'		            => '(%s)',
                'FILTER_STRUCTURE_CODE_ENTITE'	        => '(supannCodeEntite=%s)',
                'FILTER_STRUCTURE_CODE_ENTITE_PARENT'   => '(supannCodeEntiteParent=%s)',
            ],

            'log_path' => '/tmp/oscar-ldap.log'
        ]
    );

    public function testNoConfigFile(){
        $connectorLdapPerson = new \Oscar\Connector\ConnectorLdapPersonJson();

        try {
            $connectorLdapPerson->getFileConfigContent();
            $this->assertTrue(true);
        }
        catch (\Oscar\Exception\OscarException $e ){
            $this->fail("Fichier de configuration absent, devrait lever une exception");
        }
    }

    public function testParsePersonLdap(){
        $extractorLdap = new LdapExtractionStrategy(new \Zend\ServiceManager\ServiceManager());

        $person = array(
              "buildingName" => "Maison des sciences économiques",
              "cn" => "Nagot Isabelle",
              "departmentnumber" => "CNU 26",
              "displayname" => "Isabelle Nagot",
              "dn" => "uid=nagot,ou=people,dc=univ-paris1,dc=fr",
              "edupersonaffiliation" => array (
                "member",
                "teacher",
                "faculty",
                "researcher",
                "employee"
              ),
              "edupersonorgdn" => "supannCodeEntite=UP1,ou=structures,dc=univ-paris1,dc=fr",
              "edupersonorgunitdn" => array(
                "ou=U27,ou=structures,o=Paris1,dc=univ-paris1,dc=fr",
                "ou=U02C,ou=structures,o=Paris1,dc=univ-paris1,dc=fr"
              ),
              "edupersonprimaryaffiliation" => "teacher",
              "edupersonprimaryorgunitdn" => "ou=U27,ou=structures,o=Paris1,dc=univ-paris1,dc=fr",
              "edupersonprincipalname" => "nagot@univ-paris1.fr",
              "employeetype" => "Maître de conférences",
              "gecos" => "Isabelle Nagot",
              "gidnumber" => "2000000",
              "givenname" => "Isabelle",
              "info" => "Mathématiques appliquées et Sciences sociales ",
              "mail" => "Isabelle.Nagot@univ-paris1.fr",
              "postaladdress" => "106 BOULEVARD DE L'HÔPITAL$75013 PARIS\$FRANCE",
              "sn" => "Nagot",
              "supannactivite" => "{CNU}2600",
              "supannaliaslogin" => "nagot",
              "supanncivilite" =>"Mme",
              "supannentiteaffectation" => array(
                "U27",
                "U02C"
              ),
              "supannentiteaffectationprincipale" => "U27",
              "supannetablissement" => "{UAI}0751717J",
              "supannlisterouge" => "FALSE",
              "supannorganisme" => "{EES}0751717J",
              "telephonenumber" => "+33 1 44 07 82 79",
              "uid" => "nagot",
              "uidnumber" => "599381"
        );

        $personObj = $extractorLdap->parseLdapPerson($person);
        $this->assertEquals('Isabelle', $personObj['firstname']);
        $this->assertEquals('Nagot', $personObj['lastname']);
        $this->assertEquals('nagot', $personObj['login']);
        $this->assertEquals('U27', $personObj['codeHarpege']);
        $this->assertEquals('Isabelle.Nagot@univ-paris1.fr', $personObj['email']);
        $this->assertEquals('Isabelle.Nagot@univ-paris1.fr', $personObj['emailPrive']);
        $this->assertEquals('+33 1 44 07 82 79', $personObj['phone']);
        $this->assertEquals("Maison des sciences économiques", $personObj['ldapsitelocation']);
        $this->assertEquals(array('U27','U02C'), $personObj['supannentiteaffectation']);
        $this->assertEquals('nagot', $personObj['ladapLogin']);
    }

    public function testLdapConnexion(){
        try {
            $organizationLdap= new \Oscar\Entity\PersonLdap();
            $mockLdap = $this->createMock(LdapExtractionStrategy::class);
            $mockLdap->expects($this->once())
                ->method("initiateLdapPerson")
                ->with($this->settingsLdap, $this->settingsLdap["ldap"]["connection"]["default"]["params"])
                ->willReturn($organizationLdap);

            $mockLdap->initiateLdapPerson($this->settingsLdap,
                $this->settingsLdap["ldap"]["connection"]["default"]["params"]);
            $this->assertTrue(true);
        } catch (LdapException $e) {
            $this->fail("La connexion Ldap Organization a échoué");
        }
    }

    /**
     * @throws LdapException
     */
    public function testLdapResponse(){
        $person = array(
            "buildingName" => "Maison des sciences économiques",
            "cn" => "Nagot Isabelle",
            "departmentnumber" => "CNU 26",
            "displayname" => "Isabelle Nagot",
            "dn" => "uid=nagot,ou=people,dc=univ-paris1,dc=fr",
            "edupersonaffiliation" => array (
                "member",
                "teacher",
                "faculty",
                "researcher",
                "employee"
            ),
            "edupersonorgdn" => "supannCodeEntite=UP1,ou=structures,dc=univ-paris1,dc=fr",
            "edupersonorgunitdn" => array(
                "ou=U27,ou=structures,o=Paris1,dc=univ-paris1,dc=fr",
                "ou=U02C,ou=structures,o=Paris1,dc=univ-paris1,dc=fr"
            ),
            "edupersonprimaryaffiliation" => "teacher",
            "edupersonprimaryorgunitdn" => "ou=U27,ou=structures,o=Paris1,dc=univ-paris1,dc=fr",
            "edupersonprincipalname" => "nagot@univ-paris1.fr",
            "employeetype" => "Maître de conférences",
            "gecos" => "Isabelle Nagot",
            "gidnumber" => "2000000",
            "givenname" => "Isabelle",
            "info" => "Mathématiques appliquées et Sciences sociales ",
            "mail" => "Isabelle.Nagot@univ-paris1.fr",
            "postaladdress" => "106 BOULEVARD DE L'HÔPITAL$75013 PARIS\$FRANCE",
            "sn" => "Nagot",
            "supannactivite" => "{CNU}2600",
            "supannaliaslogin" => "nagot",
            "supanncivilite" =>"Mme",
            "supannentiteaffectation" => array(
                "U27",
                "U02C"
            ),
            "supannentiteaffectationprincipale" => "U27",
            "supannetablissement" => "{UAI}0751717J",
            "supannlisterouge" => "FALSE",
            "supannorganisme" => "{EES}0751717J",
            "telephonenumber" => "+33 1 44 07 82 79",
            "uid" => "nagot",
            "uidnumber" => "599381"
        );

        $personLdap= $this->createMock(\Oscar\Entity\PersonLdap::class);
        $personLdap->expects($this->once())
            ->method("findAll")
            ->with("&(objectClass=inetOrgPerson)(eduPersonAffiliation=member)(eduPersonAffiliation=researcher)")
            ->willReturn($person);

        $mockLdap = $this->createMock(LdapExtractionStrategy::class);
        $mockLdap->expects($this->once())
            ->method("initiateLdapPerson")
            ->with($this->settingsLdap, $this->settingsLdap["ldap"]["connection"]["default"]["params"])
            ->willReturn($personLdap);

        $mockLdap->initiateLdapPerson($this->settingsLdap,
            $this->settingsLdap["ldap"]["connection"]["default"]["params"]);

        $data = $personLdap->findAll(
            "&(objectClass=inetOrgPerson)(eduPersonAffiliation=member)(eduPersonAffiliation=researcher)");

        $this->assertIsArray($data);
    }

    public function testObjectPerson(){
        $extractorLdap = new LdapExtractionStrategy(new \Zend\ServiceManager\ServiceManager());

        $person = array(
            "buildingname" => "Maison des sciences économiques",
            "cn" => "Nagot Isabelle",
            "departmentnumber" => "CNU 26",
            "displayname" => "Isabelle Nagot",
            "dn" => "uid=nagot,ou=people,dc=univ-paris1,dc=fr",
            "edupersonaffiliation" => array (
                "member",
                "teacher",
                "faculty",
                "researcher",
                "employee"
            ),
            "edupersonorgdn" => "supannCodeEntite=UP1,ou=structures,dc=univ-paris1,dc=fr",
            "edupersonorgunitdn" => array(
                "ou=U27,ou=structures,o=Paris1,dc=univ-paris1,dc=fr",
                "ou=U02C,ou=structures,o=Paris1,dc=univ-paris1,dc=fr"
            ),
            "edupersonprimaryaffiliation" => "teacher",
            "edupersonprimaryorgunitdn" => "ou=U27,ou=structures,o=Paris1,dc=univ-paris1,dc=fr",
            "edupersonprincipalname" => "nagot@univ-paris1.fr",
            "employeetype" => "Maître de conférences",
            "gecos" => "Isabelle Nagot",
            "gidnumber" => "2000000",
            "givenname" => "Isabelle",
            "info" => "Mathématiques appliquées et Sciences sociales ",
            "mail" => "Isabelle.Nagot@univ-paris1.fr",
            "postaladdress" => "106 BOULEVARD DE L'HÔPITAL$75013 PARIS\$FRANCE",
            "sn" => "Nagot",
            "supannactivite" => "{CNU}2600",
            "supannaliaslogin" => "nagot",
            "supanncivilite" =>"Mme",
            "supannentiteaffectation" => array(
                "U27",
                "U02C"
            ),
            "supannentiteaffectationprincipale" => "U27",
            "supannetablissement" => "{UAI}0751717J",
            "supannlisterouge" => "FALSE",
            "supannorganisme" => "{EES}0751717J",
            "telephonenumber" => "+33 1 44 07 82 79",
            "uid" => "nagot",
            "uidnumber" => "599381"
        );

        try {
            $personData= $extractorLdap->parseLdapPerson($person);
            $personObj = (object) $personData;
            $this->assertObjectHasAttribute('firstname', $personObj);
            $this->assertObjectHasAttribute('lastname', $personObj);
            $this->assertObjectHasAttribute('codeHarpege', $personObj);
            $this->assertObjectHasAttribute('login', $personObj);
            $this->assertObjectHasAttribute('email', $personObj);
            $this->assertObjectHasAttribute('phone', $personObj);
            $this->assertObjectHasAttribute('ldapsitelocation', $personObj);
            $this->assertObjectHasAttribute('supannentiteaffectation', $personObj);
            $this->assertObjectHasAttribute('ladapLogin', $personObj);
        } catch (LdapException $e) {
            $this->fail("La vérification du contenu de l'objet a échoué");
        }
    }
}
