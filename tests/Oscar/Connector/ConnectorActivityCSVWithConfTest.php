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
        $this->assertEquals(17, count($this->getDemoConfig()));
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

        // Deuxième activité
        $this->assertEquals(45000.0, $datas[1]['amount']);
        $this->assertEquals('2017-12-24', $datas[1]['datepfi']);
        $this->assertEquals('2017-12-31', $datas[1]['datesigned']);

        /// PAYMENTS
        $this->assertEquals(2, count($datas[1]['payments']));
        $this->assertEquals(20000, $datas[1]['payments'][0]['amount']);
        $this->assertEquals('2018-01-01', $datas[1]['payments'][0]['date']);
        $this->assertEquals('2018-01-01', $datas[1]['payments'][0]['predicted']);
        $this->assertEquals(25000, $datas[1]['payments'][1]['amount']);
        $this->assertEquals('', $datas[1]['payments'][1]['date']);
        $this->assertEquals('2018-06-01', $datas[1]['payments'][1]['predicted']);

        // MILESTONES
        $this->assertEquals('Rapport financier', $datas[1]['milestones'][0]['type']);
        $this->assertEquals('2018-04-15', $datas[1]['milestones'][0]['date']);
    }
}