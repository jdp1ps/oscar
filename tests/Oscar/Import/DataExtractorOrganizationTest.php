<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 07/12/2017
 * Time: 10:32
 */

namespace tests\Oscar\Import;


use Oscar\Import\Data\DataExtractorOrganization;
use PHPUnit\Framework\TestCase;


/**
 * Formalisme des organizations sous la forme d'un chaîne :
 *  - [CODE] SHORT Long Name
 *  - SHORT Long Name
 *  - Long Name
 *  - [CODE] Long Name
 * Class DataExtractorOrganizationTest
 * @package tests\Oscar\Import
 */
class DataExtractorOrganizationTest extends TestCase
{
    public function testBasicData(){
        $extractor = new DataExtractorOrganization();
        $ext = $extractor->extract('Cyberdyne Corp');
        $this->assertEquals("Cyberdyne Corp", $ext['longname']);
    }

    public function testCodeData(){
        $extractor = new DataExtractorOrganization();
        $ext = $extractor->extract('[2C] Cyberdyne Corp');
        $this->assertEquals("Cyberdyne Corp", $ext['longname']);
        $this->assertEquals("2C", $ext['code']);
    }

    public function testNoCodeData(){
        $extractor = new DataExtractorOrganization();
        $ext = $extractor->extract('Lexcorp');
        $this->assertEquals("Lexcorp", $ext['longname']);
        $this->assertEquals("", $ext['code']);
    }

    public function testShortnameData(){
        $extractor = new DataExtractorOrganization();
        $ext = $extractor->extract('LXC Lexcorp');
        $this->assertEquals("Lexcorp", $ext['longname']);
        $this->assertEquals("LXC", $ext['shortname']);
    }

    public function testShortAndCodeData(){
        $extractor = new DataExtractorOrganization();
        $ext = $extractor->extract('[C0237] CGP Cogip Entreprise');
        $this->assertEquals("Cogip Entreprise", $ext['longname']);
        $this->assertEquals("CGP", $ext['shortname']);
        $this->assertEquals("C0237", $ext['code']);
    }

    /** Données foireuses */
    public function testBadData(){
        $extractor = new DataExtractorOrganization();
        $ext = $extractor->extract('jdqldkfjdsklf');
        $this->assertEquals("jdqldkfjdsklf", $ext['longname']);
        $this->assertEquals("", $ext['code']);
        $this->assertEquals("", $ext['shortname']);
    }
}