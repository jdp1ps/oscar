<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-12-06 10:06
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import;


use Oscar\Import\Data\DataExtractorDate;
use PHPUnit\Framework\TestCase;

class DataExtractorDateTest extends TestCase
{

    public function testStandardValueExtract(){
        setlocale(E_ALL, 'FR_fr');
        $data = '2014-01-01';
        $date = (new DataExtractorDate())->extract($data);
        $this->assertNotNull($date);
        $this->assertEquals($data, $date->format('Y-m-d'));
    }

    public function testSlashYearEndValueExtract(){
        setlocale(E_ALL, 'FR_fr');
        $data = '01/01/2014';
        $date = (new DataExtractorDate())->extract($data);
        $this->assertNotNull($date);
        $this->assertEquals($data, $date->format('d/m/Y'));
    }

    public function testBadDateValueExtract(){
        setlocale(E_ALL, 'FR_fr');
        $data = '19/14/2014';
        $extractor = new DataExtractorDate();
        $date = $extractor->extract($data);
        $this->assertNull($date);
        $this->assertTrue($extractor->hasError());
    }
}