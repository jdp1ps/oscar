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
}