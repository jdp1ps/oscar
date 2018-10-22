<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 18/04/2018
 * Time: 13:39
 */

namespace Oscar\Utils;

use PHPUnit\Framework\TestCase;

class DateTimeUtilsTest extends TestCase
{
    public function testSDateOK(){


        $valid = [
            ['date' => '2018-12', 'month' => 12, 'year' => 2018 ],
            ['date' => '2018-01', 'month' => 1, 'year' => 2018 ],
            ['date' => '2018-1', 'month' => 1, 'year' => 2018 ],
            ['date' => '2018-02', 'month' => 2, 'year' => 2018 ],
            ['date' => '2018-2', 'month' => 2, 'year' => 2018 ],
        ];

        foreach ($valid as $dt){
            $result = DateTimeUtils::extractPeriodDatasFromString($dt['date']);
            $this->assertEquals(3, count($result));
        }
    }
}