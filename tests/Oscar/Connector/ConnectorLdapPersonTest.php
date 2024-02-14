<?php

use PHPUnit\Framework\TestCase;

class ConnectorLdapPersonTest extends TestCase
{
    public function testNoFile(){
        $connecteurLdapPerson = new \Oscar\Connector\ConnectorLdapPersonJson();

        try {
            $connecteurLdapPerson->getFileConfig();
            $this->fail("Fichier de configuration absent, devrait lever une exception");
        }
        catch (\Oscar\Exception\OscarException $e ){
            $this->assertEquals(true, true);
        }
    }

    public function testExecute(){
        $connecteurLdapPerson = new \Oscar\Connector\ConnectorLdapPersonJson();

        try {
            $connecteurLdapPerson->execute();
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail("L'exécution du connecteur a échoué");
        }
    }

    public function testEmptyFile(){
        $connecteurLdapPerson = new \Oscar\Connector\ConnectorLdapPersonJson();

        try {
            $datas = $connecteurLdapPerson->getFileConfig();
            $this->assertNull($datas);
        }
        catch (\Oscar\Exception\OscarException $e ){
            $this->fail("Les fichiers vides doivent retourner NULL");
        }
    }

    public function testNotJsonFile(){
        $connecteurLdapPerson = new \Oscar\Connector\ConnectorLdapPersonJson();

        try {
            $datas = $connecteurLdapPerson->getFileConfig();
            $this->fail("Non-YAML file must throw exception !");
        }
        catch (\Oscar\Connector\NotYamlFileException $e ){
            $this->assertTrue(true);
        }
    }
}
