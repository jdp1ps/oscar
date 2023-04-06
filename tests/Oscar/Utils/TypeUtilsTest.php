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

class TypeUtilsTest extends TestCase
{
    public function testOK(){
        $this->assertEquals(1, TypeUtils::getIntegerFromString("1"), "1 > 1");
        $this->assertEquals(1, TypeUtils::getIntegerFromString("001", "001 > 1"));
        $this->assertEquals(999, TypeUtils::getIntegerFromString("999"));
    }
    public function testException1(){
        $this->expectException(OscarTypeException::class);
        $foo = TypeUtils::getIntegerFromString("ABCD");
    }
}