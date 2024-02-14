<?php

use Oscar\Connector\DataExtractionStrategy\LdapExtractionStrategy;
use PHPUnit\Framework\TestCase;

class ConnectorLdapOrganizationTest extends TestCase
{
    public function testNoFile(){
        $connecteurLdapPerson = new \Oscar\Connector\ConnectorLdapOrganizationJson();

        try {
            $connecteurLdapPerson->getFileConfig();
            $this->fail("Fichier de configuration absent, devrait lever une exception");
        }
        catch (\Oscar\Exception\OscarException $e ){
            $this->assertTrue(true);
        }
    }

    public function testExecute(){
        $extractorLdap = new LdapExtractionStrategy(new \Zend\ServiceManager\ServiceManager());
        
        $organization = array(
            ["businesscategory"] => "research",
            ["description"] => "ACTE: Arts, créations, théories, esthétique (UR 7539)",
            ["dn"] => "supannCodeEntite=UR049_4,ou=structures,dc=univ-paris1,dc=fr",
            ["labeleduri"] => "https://institut-acte.pantheonsorbonne.fr/",
            ["ou"] => "UR 7539 - ACTE",
            ["postaladdress"] => "Centre Saint Charles$162 RUE SAINT-CHARLES$75015 PARIS\$France",
            ["supanncodeentite"] => "UR049_4",
            ["supanncodeentiteparent"] => "UR04",
            ["supannrefid"] => array(
              "{RNSR}201220422A",
              "{APOGEE.EQR}UR049_4",
              "{SIHAM.UO}UR049_4",
              "{SINAPS:STRUC}UR049_4",
            ),
            ["supanntypeentite"] => "{SUPANN}S311",
            ["telephonenumber"] => "+33 1 44 07 84 40"
        );

        try {
            $org = $extractorLdap->parseLdapOrganization($organization);
            $this->assertEquals("ACTE: Arts, créations, théories, esthétique (UR 7539)", $org['name']);
            $this->assertEquals("UR049_4", $org['code']);
            $this->assertEquals("UR 7539 - ACTE", $org['shortname']);
            $this->assertEquals("ACTE: Arts, créations, théories, esthétique (UR 7539)", $org['longname']);
            $this->assertEquals("+33 1 44 07 84 40", $org['phone']);
            $this->assertEquals("ACTE: Arts, créations, théories, esthétique (UR 7539)", $org['description']);
            $this->assertEquals("https://institut-acte.pantheonsorbonne.fr/", $org['url']);
            $this->assertEquals("{RNSR}201220422A", $org['rnsr']);
            $this->assertEquals("supanncodeentite", $org['ldapsupanncodeentite']);
            $this->assertEquals(
                array(
                    "address1" => "Centre Saint Charles",
                    "address2" => "162 RUE SAINT-CHARLES",
                    "zipcode" => "75015",
                    "country" => "France",
                    "city" => "PARIS"
                ),
                $org['address']
            );

        } catch (Exception $e) {
            $this->fail("Le remplissage du tableau pour hydratation a échoué");
        }
    }

    public function testLdapConnexion(){
        $serviceManager = new Zend\ServiceManager\ServiceManager();
        $moduleOptions = $serviceManager->get('unicaen-app_module_options');

        $configLdap = $moduleOptions->getLdap();
        $ldap = $configLdap['connection']['default']['params'];

        $extractorLdap = new LdapExtractionStrategy(new \Zend\ServiceManager\ServiceManager());


        try {
            $connectorLdapOrganization = $extractorLdap->initiateLdapOrganization($configLdap, $ldap);
            $this->assertTrue(true);
        } catch (\Zend\Ldap\Exception\LdapException $e) {
            $this->fail("La connexion Ldap Organization a échoué");
        }
    }
}
