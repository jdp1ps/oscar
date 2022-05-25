<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 18/04/2018
 * Time: 13:39
 */

namespace Oscar\Entity;

use Oscar\Exception\OscarException;
use PHPUnit\Framework\TestCase;

class ActivityTest extends TestCase
{

    /**
     * @return ValidationPeriod
     */
    public function testDateStart(){
        $activity = new Activity();
        $dateStr = $activity->getDateStartStr();
        $this->assertEquals(null, $activity->getDateStart());
        $this->assertEquals("", $dateStr);

    }
}