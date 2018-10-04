<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 18/04/2018
 * Time: 13:42
 */

namespace tests\Oscar\Connector;


use Oscar\Connector\ConnectorActivityCSVWithConf;
use Oscar\Import\Data\DataExtractorFullname;
use PHPUnit\Framework\TestCase;

class DataExtractorFullnameTest extends TestCase
{

    /**
     * Prénom simple
     */
    public function testSimple()
    {
        $input = 'Stéphane Bouvry';
        $extractor = new DataExtractorFullname();

        $datas = $extractor->extract($input);

        $this->assertEquals('Stéphane', $datas['firstname']);
        $this->assertEquals('Bouvry', $datas['lastname']);
        $this->assertEquals('Stéphane Bouvry', $datas['fullname']);
        $this->assertEquals('', $datas['email']);
    }

    /**
     * Simple avec Email
     */
    public function testSimpleAvecEmail()
    {
        $input = 'Stéphane Bouvry <stephane.bouvry@unicaen.fr>';
        $extractor = new DataExtractorFullname();

        $datas = $extractor->extract($input);

        $this->assertEquals('Stéphane', $datas['firstname']);
        $this->assertEquals('Bouvry', $datas['lastname']);
        $this->assertEquals('Stéphane Bouvry', $datas['fullname']);
        $this->assertEquals('stephane.bouvry@unicaen.fr', $datas['email']);
    }

    /**
     * Prénom composé avec un tiret
     */
    public function testPrénomComposé()
    {
        $input = 'Jean-Claude Dus';
        $extractor = new DataExtractorFullname();

        $datas = $extractor->extract($input);

        $this->assertEquals('Jean-Claude', $datas['firstname']);
        $this->assertEquals('Dus', $datas['lastname']);
        $this->assertEquals('Jean-Claude Dus', $datas['fullname']);
        $this->assertEquals('', $datas['email']);
    }

    /**
     * Prénom composé avec un tiret
     */
    public function testPrénomComposéAvecEmail()
    {
        $input = 'Jean-Claude Dus <jean-claude.dus@unicaen.fr>';
        $extractor = new DataExtractorFullname();

        $datas = $extractor->extract($input);

        $this->assertEquals('Jean-Claude', $datas['firstname']);
        $this->assertEquals('Dus', $datas['lastname']);
        $this->assertEquals('Jean-Claude Dus', $datas['fullname']);
        $this->assertEquals('jean-claude.dus@unicaen.fr', $datas['email']);
    }

    /**
     * BUG : Identifié par Damien Rieu sur les nom de famille avec espace
     */
    public function testNomEspace()
    {
        $input = 'Julie Le Carpentier';
        $extractor = new DataExtractorFullname();

        $datas = $extractor->extract($input);

        $this->assertEquals('Julie', $datas['firstname']);
        $this->assertEquals('Le Carpentier', $datas['lastname']);
        $this->assertEquals('Julie Le Carpentier', $datas['fullname']);
        $this->assertEquals('', $datas['email']);
    }
}