<?php

use Oscar\Connector\DataExtractionStrategy\LdapExtractionStrategy;
use PHPUnit\Framework\TestCase;

class ConnectorLdapPersonTest extends TestCase
{
    public function testNoFile(){
        $connecteurLdapPerson = new \Oscar\Connector\ConnectorLdapPersonJson();

        try {
            $connecteurLdapPerson->getFileConfigContent();
            $this->fail("Fichier de configuration absent, devrait lever une exception");
        }
        catch (\Oscar\Exception\OscarException $e ){
            $this->assertEquals(true, true);
        }
    }

    public function testParsePersonLdap(){
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
            $personObj = $extractorLdap->parseLdapPerson($person);
            $this->assertEquals('Isabelle', $personObj['firstname']);
            $this->assertEquals('Nagot', $personObj['lastname']);
            $this->assertEquals('uid', $personObj['login']);
            $this->assertEquals('U27', $personObj['codeHarpege']);
            $this->assertEquals('Isabelle.Nagot@univ-paris1.fr', $personObj['email']);
            $this->assertEquals('Isabelle.Nagot@univ-paris1.fr', $personObj['emailPrive']);
            $this->assertEquals('+33 1 44 07 82 79', $personObj['phone']);
            $this->assertEquals('Maison des sciences économiques', $personObj['ldapsitelocation']);
            $this->assertEquals('U27,U02C', $personObj['supannentiteaffectation']);
            $this->assertEquals('nagot', $personObj['ladapLogin']);

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
            $connectorLdapPerson = $extractorLdap->initiateLdapPerson($configLdap, $ldap);
            $this->assertTrue(true);
        } catch (\Zend\Ldap\Exception\LdapException $e) {
            $this->fail("La connexion Ldap Person a échoué");
        }
    }

    public function testLdapFilter(){
        $serviceManager = new Zend\ServiceManager\ServiceManager();
        $moduleOptions = $serviceManager->get('unicaen-app_module_options');

        $configLdap = $moduleOptions->getLdap();
        $ldap = $configLdap['connection']['default']['params'];

        $extractorLdap = new LdapExtractionStrategy(new \Zend\ServiceManager\ServiceManager());

        try {
            $connectorLdapPerson = $extractorLdap->initiateLdapPerson($configLdap, $ldap);
            $data = $connectorLdapPerson->findAll("&(objectClass=inetOrgPerson)(eduPersonAffiliation=member)(eduPersonAffiliation=researcher)");
            $this->assertIsArray($data);
        } catch (\Zend\Ldap\Exception\LdapException $e) {
            $this->fail("L'objet renvoyé par l'annuaire LDAP n'est pas un tableau");
        }
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
        } catch (\Zend\Ldap\Exception\LdapException $e) {
            $this->fail("La vérification du contenu de l'objet a échoué");
        }
    }
}
