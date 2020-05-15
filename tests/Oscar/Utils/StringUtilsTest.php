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

class StringUtilsTest extends TestCase
{
    public function testSDateOK(){
        $this->assertEquals("60000000", StringUtils::feedString('6'));
        $this->assertEquals("63320000", StringUtils::feedString('6332'));
        $this->assertEquals("00000000", StringUtils::feedString(''));
        $this->assertEquals("00000000", StringUtils::feedString(null));
        $this->assertEquals("40000000", StringUtils::feedString(4));
    }

    public function testPurgeZero(){
        //$this->assertEquals("6", StringUtils::purgeZero('00600'));
    }
}