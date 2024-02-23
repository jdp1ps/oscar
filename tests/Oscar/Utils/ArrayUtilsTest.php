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
use Zend\Db\Sql\Ddl\Column\Datetime;

class ArrayUtilsTest extends TestCase
{
    public function testOK(){
        $this->assertEquals([1,2,3], ArrayUtils::explodeIntegerFromString('1,2,3'));
        $this->assertEquals([7], ArrayUtils::explodeIntegerFromString('7'));
    }

    public function testException1(){
        $this->expectException(OscarTypeException::class);
        $foo = ArrayUtils::explodeIntegerFromString('pas un int');
    }

    public function testArrayInt(){
        $array = ['11', '9', '8'];
        $expected = [11,9,8];
        $result = ArrayUtils::normalizeArray($array);
        $this->assertEquals($expected, $result);

        $array = ['0', '9', '8'];
        $expected = [0,9,8];
        $result = ArrayUtils::normalizeArray($array);
        $this->assertEquals($expected, $result);

        $array = ['abc', '9', '8'];
        $expected = [9,8];
        $result = ArrayUtils::normalizeArray($array);
        $this->assertEquals($expected, $result);

        $array = ['abc', '9', '0'];
        $expected = [9];
        $result = ArrayUtils::normalizeArray($array, true);
        $this->assertEquals($expected, $result);
    }
}