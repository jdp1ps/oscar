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
            $this->assertTrue(true);
        }
    }

    public function testExecute(){
        $connecteurLdapPerson = new \Oscar\Connector\ConnectorLdapOrganizationJson();

        try {
            $connecteurLdapPerson->execute();
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail("L'exécution du connecteur a échoué");
        }
    }

    public function testEmptyFile(){
        $connecteurLdapPerson = new \Oscar\Connector\ConnectorLdapOrganizationJson();

        try {
            $datas = $connecteurLdapPerson->getFileConfigContent();
            $this->assertNull($datas);
        }
        catch (\Oscar\Exception\OscarException $e ){
            $this->fail("Les fichiers vides doivent retourner NULL");
        }
    }

    public function testNotJsonFile(){
        $connecteurLdapPerson = new \Oscar\Connector\ConnectorLdapOrganizationJson();

        try {
            $datas = $connecteurLdapPerson->getFileConfigContent();
            $this->fail("Non-YAML file must throw exception !");
        }
        catch (\Oscar\Connector\NotYamlFileException $e ){
            $this->assertTrue(true);
        }
    }
}
