<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-17 10:17
 * @copyright Certic (c) 2017
 */

use PHPUnit\Framework\TestCase;

class GetJsonDataFromFileStrategyTest extends TestCase
{
    public function testNoFile(){
        $file = 'fichier-inconnue.json';

        $jsonDataReader = new \Oscar\Connector\GetJsonDataFromFileStrategy($file);

        try {
            $jsonDataReader->getAll();
            $this->fail("Fichier absent, devrait lever une exception");
        }
        catch (\Oscar\Exception\OscarException $e ){
            $this->assertEquals(true, true);
        }
    }

    public function testEmptyFile(){
        $file = __DIR__.'/empty-file.json';
        $jsonDataReader = new \Oscar\Connector\GetJsonDataFromFileStrategy($file);

        try {
            $datas = $jsonDataReader->getAll();
            $this->assertNull($datas);
        }
        catch (\Oscar\Exception\OscarException $e ){
            $this->fail("Les fichiers vides doivent retourner NULL");
        }
    }

    public function testNotJsonFile(){
        $file = __DIR__.'/not-json-file.json';
        $jsonDataReader = new \Oscar\Connector\GetJsonDataFromFileStrategy($file);

        try {
            $datas = $jsonDataReader->getAll();
            $this->fail("Non-JSON file must throw exception !");
        }
        catch (\Oscar\Connector\NotJsonFileException $e ){
            $this->assertTrue(true);
        }
    }
}
