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

        $out = $extractor->extract("Jean-Claude Dus");
        $this->assertEquals("Jean-Claude", $out['firstname']);
        $this->assertEquals("Dus", $out['lastname']);
    }
}