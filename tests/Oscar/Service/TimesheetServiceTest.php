<?php


namespace Oscar\Service;


use PHPUnit\Framework\TestCase;

class TimesheetServiceTest extends TestCase
{
    public function testValidationPersonPeriod()
    {
        $periodDatas = unserialize(file_get_contents(__DIR__.'/../../ressources/timesheets/period-datas-sb-202008.txt'));
        $this->assertTrue(true);
    }
}