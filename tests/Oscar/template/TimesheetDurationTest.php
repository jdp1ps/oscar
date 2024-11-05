<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 18/04/2018
 * Time: 13:39
 */


use Oscar\Exception\OscarException;
use PHPUnit\Framework\TestCase;
use Zend\Db\Sql\Ddl\Column\Datetime;

require_once __DIR__ . "/../../../data/templates/functions.inc.php";

class TimesheetDurationTest extends TestCase
{


    public function testDuration()
    {
        $this->assertEquals(
            "8:00",
            duration(8)
        );

        $this->assertEquals(
            "4:30",
            duration(4.5)
        );

        $this->assertEquals(
            "4:15",
            duration(4.25)
        );

        $this->assertEquals(
            "7:24",
            duration(7.4)
        );
    }

    public function testDurationBadValue()
    {
//        $result = duration("");
//        die("ICI : $result");
        $this->assertEquals(
            "0:00",
            duration("")
        );
        $this->assertEquals(
            "8:00",
            duration("8")
        );
        $this->assertEquals(
            "0:00",
            duration("non-numeric")
        );
    }
}