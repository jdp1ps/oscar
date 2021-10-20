<?php

use \PHPUnit\Framework\TestCase;

/**
 * Class PeriodInfosTest
 */
class PeriodInfosTest extends TestCase
{

    public function testOne()
    {
        $periodStr = '2001-01';
        $periodInfo = \Oscar\Utils\PeriodInfos::getPeriodInfosObj($periodStr);

        $this->assertEquals(2001, $periodInfo->getYear());
        $this->assertEquals(1, $periodInfo->getMonth());
        $this->assertEquals('2001-01-01 00:00:00', $periodInfo->getStart()->format('Y-m-d H:i:s'));
        $this->assertEquals('2001-01-31 23:59:59', $periodInfo->getEnd()->format('Y-m-d H:i:s'));
        $this->assertEquals(31, $periodInfo->getTotalDays());

    }

    public function testPrevious()
    {
        $periodStr = '2001-01';
        $periodInfo = \Oscar\Utils\PeriodInfos::getPeriodInfosObj($periodStr);


        $this->assertEquals(2001, $periodInfo->getYear());
        $this->assertEquals(1, $periodInfo->getMonth());
        $this->assertEquals('2001-01-01 00:00:00', $periodInfo->getStart()->format('Y-m-d H:i:s'));
        $this->assertEquals('2001-01-31 23:59:59', $periodInfo->getEnd()->format('Y-m-d H:i:s'));
        $this->assertEquals(31, $periodInfo->getTotalDays());

        $periodInfo->prevMonth()->prevMonth();

        $this->assertEquals(2000, $periodInfo->getYear());
        $this->assertEquals(11, $periodInfo->getMonth());
        $this->assertEquals('2000-11-01 00:00:00', $periodInfo->getStart()->format('Y-m-d H:i:s'));
        $this->assertEquals('2000-11-30 23:59:59', $periodInfo->getEnd()->format('Y-m-d H:i:s'));
        $this->assertEquals(30, $periodInfo->getTotalDays());

    }
}
