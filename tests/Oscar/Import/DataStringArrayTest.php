<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-12-06 10:06
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import;


use Oscar\Exception\OscarException;
use Oscar\Import\Data\DataStringArray;
use PHPUnit\Framework\TestCase;

class DataStringArrayTest extends TestCase
{

    public function testInputNull(){
        $input = null;
        $converter = new DataStringArray();
        try {
            $converter->extract($input);
            $this->assertTrue(false, "Ne devrait pas passer");
        } catch (OscarException $exception){
            $this->assertEquals(_('Type de donnée inattendue'), $exception->getMessage());
        }
    }

    public function testInputEmpty(){
        $input = "";
        $converter = new DataStringArray();
        $output = $converter->extract($input);
        $this->assertIsArray($output);
        $this->assertEquals(0, count($output));
    }

    public function testInputEmptySpace(){
        $input = " ";
        $converter = new DataStringArray();
        $output = $converter->extract($input);
        $this->assertIsArray($output);
        $this->assertEquals(0, count($output));
    }

    public function testInputOne(){
        $input = "ONE";
        $converter = new DataStringArray();
        $output = $converter->extract($input);
        $this->assertEquals(1, count($output));
        $this->assertEquals($input, $output[0]);
    }

    public function testInputMore(){
        $input = "ONE,TWO,TREE";
        $converter = new DataStringArray();
        $output = $converter->extract($input);
        $this->assertEquals(3, count($output));
        $this->assertEquals("ONE", $output[0]);
        $this->assertEquals("TWO", $output[1]);
        $this->assertEquals("TREE", $output[2]);
    }

    public function testInputTrimValues(){
        $input = "ONE , TWO , TREE";
        $converter = new DataStringArray();
        $output = $converter->extract($input);
        $this->assertEquals(3, count($output));
        $this->assertEquals("ONE", $output[0]);
        $this->assertEquals("TWO", $output[1]);
        $this->assertEquals("TREE", $output[2]);
    }

    public function testInputUnique(){
        $input = "ONE , TWO , TREE, TREE, ONE, TWO, TWO";
        $converter = new DataStringArray();
        $output = $converter->extract($input);
        $this->assertEquals(3, count($output));
        $this->assertEquals("ONE", $output[0]);
        $this->assertEquals("TWO", $output[1]);
        $this->assertEquals("TREE", $output[2]);
    }
}