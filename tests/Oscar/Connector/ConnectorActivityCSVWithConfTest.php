<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 18/04/2018
 * Time: 13:42
 */

namespace tests\Oscar\Connector;


use Oscar\Connector\ConnectorActivityCSVWithConf;
use Oscar\Entity\Activity;
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
        $this->assertEquals(26, count($this->getDemoConfig()), "La configuration de démo contient 25 entrées.");
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
        $this->assertEquals(3, count($datas));

        $this->assertEquals('R1', $datas[0]['uid']);
        $this->assertEquals('RELATIV', $datas[0]['acronym']);
        $this->assertEquals('Théorie de la gravitation', $datas[0]['projectlabel']);
        $this->assertEquals("Description 1", $datas[0]['description']);
        $this->assertEquals('EOTP201400002', $datas[0]['pfi']);
        $this->assertEquals(2, count($datas[0]['organizations']['Laboratoire']));
        $this->assertEquals("Olympia", $datas[0]['organizations']['Laboratoire'][0]);
        $this->assertEquals("US Robot", $datas[0]['organizations']['Laboratoire'][1]);
        $this->assertEquals(2, count($datas[0]['persons']['Participants']), 'Valeurs multiples séparées par une virgule');
        $this->assertEquals("Batman", $datas[0]['persons']['Participants'][0]);
        $this->assertEquals("Robin", $datas[0]['persons']['Participants'][1]);
        $this->assertEquals(2, count($datas[0]['persons']['Ingénieur']), 'Valeurs multiples sur plusieurs colonnes, une des colonne vide');
        $this->assertEquals("HEUZE VOURC'H NATHALIE", $datas[0]['persons']['Ingénieur'][0], 'Ingé 1');
        $this->assertEquals("Serge Le Normand", $datas[0]['persons']['Ingénieur'][1], 'Ingé 2');
        $this->assertEquals(null, $datas[0]['tva']);
        $this->assertEquals(null, $datas[0]['assietteSubventionnable']);
        $this->assertEquals('Recette', $datas[0]['financialImpact']);
        $this->assertEquals(null, $datas[0]['currency']);
        $this->assertEquals(Activity::STATUS_ERROR_STATUS, $datas[0]['status']);


        // Deuxième activité
        $this->assertEquals(45000.0, $datas[1]['amount']);
        $this->assertEquals("Description 2", $datas[1]['description']);
        $this->assertEquals('2017-12-24', $datas[1]['datepfi']);
        $this->assertEquals('2017-12-31', $datas[1]['datesigned']);
        $this->assertEquals(2, count($datas[1]['persons']['Ingénieur']), 'Valeurs multiples sur plusieurs colonnes');
        //$this->assertTrue(in_array("Marcel Grossmann", $datas[1]['persons']['Ingénieur']), "Marcel Grossmann est dans l'activité 2");
        $this->assertEquals(3, count($datas[1]['payments']));
        $this->assertEquals(20000, $datas[1]['payments'][0]['amount']);
        $this->assertEquals('2018-01-06', $datas[1]['payments'][0]['date']);
        $this->assertEquals('2018-01-01', $datas[1]['payments'][0]['predicted']);
        $this->assertEquals(25000, $datas[1]['payments'][1]['amount']);
        $this->assertEquals('', $datas[1]['payments'][1]['date']);
        $this->assertEquals('2018-06-01', $datas[1]['payments'][1]['predicted']);
        $this->assertEquals(666.66, $datas[1]['payments'][2]['amount']);
        $this->assertNull($datas[1]['payments'][2]['predicted']);
        $this->assertEquals('2020-12-31', $datas[1]['payments'][2]['date']);
        $this->assertEquals('Rapport financier', $datas[1]['milestones'][0]['type']);
        $this->assertEquals('2018-04-15', $datas[1]['milestones'][0]['date']);
        $this->assertEquals(null, $datas[1]['tva']);
        $this->assertEquals(15.0, $datas[1]['assietteSubventionnable']);
        $this->assertEquals('Dépense', $datas[1]['financialImpact']);
        $this->assertEquals('$', $datas[1]['currency']);
        $this->assertEquals(Activity::STATUS_PROGRESS, $datas[1]['status']);

        // Troisième activité
        $this->assertEquals(15000.0, $datas[2]['amount']);
        $this->assertEquals("Mécanique quantique", $datas[2]['description']);
        $this->assertEquals("Niels Bohr", $datas[2]['persons']['Ingénieur'][0]);
        $this->assertEquals(19.6, $datas[2]['tva']);
        $this->assertEquals(5.0, $datas[2]['assietteSubventionnable']);
        $this->assertEquals('Aucune', $datas[2]['financialImpact']);
        $this->assertEquals('Yens', $datas[2]['currency']);
        $this->assertEquals(Activity::STATUS_ACTIVE, $datas[2]['status']);
    }
}