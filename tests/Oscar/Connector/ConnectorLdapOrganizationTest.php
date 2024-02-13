<?php

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
            $this->assertEquals(true, true);
        }
    }

    public function testEmptyFile(){
        $connecteurLdapPerson = new \Oscar\Connector\ConnectorLdapOrganizationJson();

        try {
            $datas = $connecteurLdapPerson->getFileConfig();
            $this->assertNull($datas);
        }
        catch (\Oscar\Exception\OscarException $e ){
            $this->fail("Les fichiers vides doivent retourner NULL");
        }
    }

    public function testNotJsonFile(){
        $connecteurLdapPerson = new \Oscar\Connector\ConnectorLdapOrganizationJson();

        try {
            $datas = $connecteurLdapPerson->getFileConfig();
            $this->fail("Non-YAML file must throw exception !");
        }
        catch (\Oscar\Connector\NotYamlFileException $e ){
            $this->assertTrue(true);
        }
    }
}
