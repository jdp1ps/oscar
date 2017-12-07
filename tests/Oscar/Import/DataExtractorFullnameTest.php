<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 07/12/2017
 * Time: 10:11
 */

namespace tests\Oscar\Import;


use Oscar\Import\Data\DataExtractorFullname;
use PHPUnit\Framework\TestCase;

class DataExtractorFullnameTest extends TestCase
{
    public function testValid(){
        $extractor = new DataExtractorFullname();

        $out = $extractor->extract("Stéphane Bouvry");
        $this->assertEquals("Stéphane", $out['firstname']);
        $this->assertEquals("Bouvry", $out['lastname']);
        $this->assertEquals("", $out['email']);
    }


    public function testCommatFirst(){
        $extractor = new DataExtractorFullname();

        $out = $extractor->extract("Jean-Claude Dus");
        $this->assertEquals("Jean-Claude", $out['firstname']);
        $this->assertEquals("Dus", $out['lastname']);
        $this->assertEquals("", $out['email']);
    }

    public function testCommatLastValid(){
        $extractor = new DataExtractorFullname();

        $out = $extractor->extract("Stéphane Pierre-Henry");
        $this->assertEquals("Stéphane", $out['firstname']);
        $this->assertEquals("Pierre-Henry", $out['lastname']);
        $this->assertEquals("", $out['email']);
    }

    public function testMailLastValid(){
        $extractor = new DataExtractorFullname();

        $out = $extractor->extract("Stéphane Brochet <stephane.brochet@email.com>");
        $this->assertEquals("Stéphane", $out['firstname']);
        $this->assertEquals("Brochet", $out['lastname']);
        $this->assertEquals("stephane.brochet@email.com", $out['email']);
    }
}