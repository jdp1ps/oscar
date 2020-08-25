<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 18/04/2018
 * Time: 13:39
 */

namespace Oscar\Utils;

use Oscar\Exception\OscarException;
use PHPUnit\Framework\TestCase;

class ValidatorInputTest extends TestCase
{
    public function testFrequency(){

        $input = 'Lun8,Lun10';

        $frequency = ValidationInput::frequency($input);

        $this->assertEquals(2, count($frequency));
        $this->assertEquals('Lun8', $frequency[0]);
        $this->assertEquals('Lun10', $frequency[1]);

    }

    public function testFrequencyInvalid(){
        $input = ',Lun8,Lun10';
        $frequency = ValidationInput::frequency($input);
        $this->assertEquals(2, count($frequency));
        $this->assertEquals('Lun8', $frequency[0]);
        $this->assertEquals('Lun10', $frequency[1]);

        $input = ',Lun8,Lun10,Bun26';
        $frequency = ValidationInput::frequency($input);
        $this->assertEquals(2, count($frequency));
        $this->assertEquals('Lun8', $frequency[0]);
        $this->assertEquals('Lun10', $frequency[1]);

        $input = ',Lun8,Lun10,Lun26';
        $frequency = ValidationInput::frequency($input);
        $this->assertEquals(2, count($frequency));
        $this->assertEquals('Lun8', $frequency[0]);
        $this->assertEquals('Lun10', $frequency[1]);
    }
}