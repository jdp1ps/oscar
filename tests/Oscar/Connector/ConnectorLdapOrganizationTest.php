<?php

use Oscar\Connector\ConnectorRepport;
use Oscar\Connector\DataExtractionStrategy\LdapExtractionStrategy;
use Oscar\Entity\Organization;
use PHPUnit\Framework\TestCase;

class ConnectorLdapOrganizationTest extends TestCase
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
        $connectorLdapPerson = new \Oscar\Connector\ConnectorLdapOrganizationJson();

        try {
            $connectorLdapPerson->getFileConfigContent();
            $this->assertTrue(true);
        }
        catch (\Oscar\Exception\OscarException $e ){
            $this->fail("Fichier de configuration absent, devrait lever une exception");
        }
    }

    public function testParseResponseLdap(){
        $extractorLdap = new LdapExtractionStrategy(new \Zend\ServiceManager\ServiceManager());
        
        $organization = array(
            "businesscategory" => "research",
            "description" => "ACTE: Arts, créations, théories, esthétique (UR 7539)",
            "dn" => "supannCodeEntite=UR049_4,ou=structures,dc=univ-paris1,dc=fr",
            "labeleduri" => "https://institut-acte.pantheonsorbonne.fr/",
            "ou" => "UR 7539 - ACTE",
            "postaladdress" => "Centre Saint Charles$162 RUE SAINT-CHARLES$75015 PARIS\$France",
            "supanncodeentite" => "UR049_4",
            "supanncodeentiteparent" => "UR04",
            "supannrefid" => array(
              "{RNSR}201220422A",
              "{APOGEE.EQR}UR049_4",
              "{SIHAM.UO}UR049_4",
              "{SINAPS:STRUC}UR049_4",
            ),
            "supanntypeentite" => "{SUPANN}S311",
            "telephonenumber" => "+33 1 44 07 84 40"
        );

        $org = $extractorLdap->parseOrganizationLdap($organization);
        $this->assertEquals("ACTE: Arts, créations, théories, esthétique (UR 7539)", $org['name']);
        $this->assertEquals("UR049_4", $org['code']);
        $this->assertEquals("UR 7539 - ACTE", $org['shortname']);
        $this->assertEquals("ACTE: Arts, créations, théories, esthétique (UR 7539)", $org['longname']);
        $this->assertEquals("+33 1 44 07 84 40", $org['phone']);
        $this->assertEquals("ACTE: Arts, créations, théories, esthétique (UR 7539)", $org['description']);
        $this->assertEquals("https://institut-acte.pantheonsorbonne.fr/", $org['url']);
        $this->assertEquals("{RNSR}201220422A", $org['rnsr']);
        $this->assertEquals("UR049_4", $org['ldapsupanncodeentite']);
        $this->assertEquals("Centre Saint Charles", $org['address']->address1);
        $this->assertEquals("162 RUE SAINT-CHARLES", $org['address']->address2);
        $this->assertEquals("75015", $org['address']->zipcode);
        $this->assertEquals("France", $org['address']->country);
        $this->assertEquals("PARIS", $org['address']->city);
    }

    public function testLdapConnexion(){
        try {
            $organizationLdap= new \Oscar\Entity\OrganizationLdap();
            $mockLdap = $this->createMock(LdapExtractionStrategy::class);
            $mockLdap->expects($this->once())
                ->method("initiateLdapOrganization")
                ->with($this->settingsLdap, $this->settingsLdap["ldap"]["connection"]["default"]["params"])
                ->willReturn($organizationLdap);

            $mockLdap->initiateLdapOrganization($this->settingsLdap,
                $this->settingsLdap["ldap"]["connection"]["default"]["params"]);
            $this->assertTrue(true);
        } catch (\Zend\Ldap\Exception\LdapException $e) {
            $this->fail("La connexion Ldap Organization a échoué");
        }
    }

    public function testLdapResponse(){
        $organization = array(
            "businesscategory" => "research",
            "description" => "ACTE: Arts, créations, théories, esthétique (UR 7539)",
            "dn" => "supannCodeEntite=UR049_4,ou=structures,dc=univ-paris1,dc=fr",
            "labeleduri" => "https://institut-acte.pantheonsorbonne.fr/",
            "ou" => "UR 7539 - ACTE",
            "postaladdress" => "Centre Saint Charles$162 RUE SAINT-CHARLES$75015 PARIS\$France",
            "supanncodeentite" => "UR049_4",
            "supanncodeentiteparent" => "UR04",
            "supannrefid" => array(
                "{RNSR}201220422A",
                "{APOGEE.EQR}UR049_4",
                "{SIHAM.UO}UR049_4",
                "{SINAPS:STRUC}UR049_4",
            ),
            "supanntypeentite" => "{SUPANN}S311",
            "telephonenumber" => "+33 1 44 07 84 40"
        );

        $organizationLdap= $this->createMock(\Oscar\Entity\OrganizationLdap::class);
        $organizationLdap->expects($this->once())
            ->method("findOneByFilter")
            ->with("&(objectClass=supannEntite)(supannTypeEntite={SUPANN}S*)(businessCategory=research)")
            ->willReturn($organization);

        $mockLdap = $this->createMock(LdapExtractionStrategy::class);
        $mockLdap->expects($this->once())
            ->method("initiateLdapOrganization")
            ->with($this->settingsLdap, $this->settingsLdap["ldap"]["connection"]["default"]["params"])
            ->willReturn($organizationLdap);

        $mockLdap->initiateLdapOrganization($this->settingsLdap,
            $this->settingsLdap["ldap"]["connection"]["default"]["params"]);

        $data = $organizationLdap->findOneByFilter(
            "&(objectClass=supannEntite)(supannTypeEntite={SUPANN}S*)(businessCategory=research)");

        $this->assertIsArray($data);
    }

    public function testOrganizationTypes(){
        $extractorLdap = new LdapExtractionStrategy(new \Zend\ServiceManager\ServiceManager());
        $typeLabel = $extractorLdap->verifyTypes("{SUPANN}S311");
        $this->assertEquals('Composante', $typeLabel);
    }

    public function testObjectOrganization(){
        $extractorLdap = new LdapExtractionStrategy(new \Zend\ServiceManager\ServiceManager());

        $organization = array(
            "businesscategory" => "research",
            "description" => "ACTE: Arts, créations, théories, esthétique (UR 7539)",
            "dn" => "supannCodeEntite=UR049_4,ou=structures,dc=univ-paris1,dc=fr",
            "labeleduri" => "https://institut-acte.pantheonsorbonne.fr/",
            "ou" => "UR 7539 - ACTE",
            "postaladdress" => "Centre Saint Charles$162 RUE SAINT-CHARLES$75015 PARIS\$France",
            "supanncodeentite" => "UR049_4",
            "supanncodeentiteparent" => "UR04",
            "supannrefid" => array(
                "{RNSR}201220422A",
                "{APOGEE.EQR}UR049_4",
                "{SIHAM.UO}UR049_4",
                "{SINAPS:STRUC}UR049_4",
            ),
            "telephonenumber" => "+33 1 44 07 84 40"
        );

        $organizationObj = new Organization();
        $report = new ConnectorRepport();

        $org = $extractorLdap->parseOrganizationLdap($organization);
        $orgObject = $extractorLdap->hydrateOrganization($organizationObj, (object) $org, $report, null);
        $this->assertObjectHasAttribute('shortName', $orgObject);
        $this->assertObjectHasAttribute('code', $orgObject);
        $this->assertObjectHasAttribute('fullName', $orgObject);
        $this->assertObjectHasAttribute('phone', $orgObject);
        $this->assertObjectHasAttribute('description', $orgObject);
        $this->assertObjectHasAttribute('url', $orgObject);
        $this->assertObjectHasAttribute('labintel', $orgObject);
        $this->assertObjectHasAttribute('rnsr', $orgObject);
        $this->assertObjectHasAttribute('street1', $orgObject);
        $this->assertObjectHasAttribute('street2', $orgObject);
        $this->assertObjectHasAttribute('street3', $orgObject);
        $this->assertObjectHasAttribute('city', $orgObject);
        $this->assertObjectHasAttribute('zipCode', $orgObject);
    }

    public function testCheckFieldValue(){
        $extractorLdap = new LdapExtractionStrategy(new \Zend\ServiceManager\ServiceManager());

        $organization = array(
            "businesscategory" => "research",
            "description" => "ACTE: Arts, créations, théories, esthétique (UR 7539)",
            "dn" => "supannCodeEntite=UR049_4,ou=structures,dc=univ-paris1,dc=fr",
            "labeleduri" => "https://institut-acte.pantheonsorbonne.fr/",
            "ou" => "UR 7539 - ACTE",
            "postaladdress" => "Centre Saint Charles$162 RUE SAINT-CHARLES$75015 PARIS\$France",
            "supanncodeentite" => "UR049_4",
            "supanncodeentiteparent" => "UR04",
            "supannrefid" => array(
                "{RNSR}201220422A",
                "{APOGEE.EQR}UR049_4",
                "{SIHAM.UO}UR049_4",
                "{SINAPS:STRUC}UR049_4",
            ),
            "supanntypeentite" => "{SUPANN}S311",
            "telephonenumber" => "+33 1 44 07 84 40"
        );

        $organizationObj = (object) $organization;
        $orgTypeEntity = $extractorLdap->getFieldValue($organizationObj, "supanntypeentite");
        $this->assertEquals('{SUPANN}S311', $orgTypeEntity);
    }
}
