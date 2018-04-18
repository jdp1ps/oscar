<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 18/04/2018
 * Time: 13:42
 */

namespace tests\Oscar\Connector;


use Oscar\Connector\ConnectorActivityCSVWithConf;
use PHPUnit\Framework\TestCase;

class ConnectorActivityCSVWithConfTest extends TestCase
{
    private function getDemoConfig(){
        return require(__DIR__ . '/../../../install/demo/import-activites/import-activites.conf.php');
    }

    private function getDemoData(){
        $path = __DIR__ . '/../../../install/demo/import-activites/import-activites.csv';
        return fopen($path, 'r');
    }

    public function testDemoData()
    {
        $source = $this->getDemoData();
        $this->assertNotNull($source);
    }

    public function testDemoConfig()
    {
        $config = $this->getDemoConfig();
        $this->assertTrue(is_array($config));
        $this->assertEquals(16, count($this->getDemoConfig()));
    }



    public function testDemo()
    {
        $source = $this->getDemoData();
        $config = $this->getDemoConfig();
        $skip = 1;

        while ($skip > 0) {
            fgetcsv($source);
            $skip--;
        }

        $converter = new ConnectorActivityCSVWithConf($source, $config, null);
        $datas = $converter->syncAll();

        $this->assertNotNull($datas);
        $this->assertEquals(2, count($datas));

        $this->assertEquals('R1', $datas[0]['uid']);
        $this->assertEquals('RELATIV', $datas[0]['acronym']);
        $this->assertEquals('EOTP201400002', $datas[0]['pfi']);
        $this->assertEquals(2, count($datas[0]['organizations']['Laboratoire']));

        $this->assertEquals("Olympia", $datas[0]['organizations']['Laboratoire'][0]);
        $this->assertEquals("US Robot", $datas[0]['organizations']['Laboratoire'][1]);


//        $converter = new ConnectorActivityCSVWithConf($source,  )
    }
}