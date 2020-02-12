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

class DateTimeUtilsTest extends TestCase
{
    public function testSDateOK(){
        $valid = [
            ['date' => '2018-01', 'month' => 1, 'year' => 2018, 'period' => '2018-1', 'periodCode' => '2018-01', 'periodLabel' => 'janvier 2018' ],
            ['date' => '2018-1', 'month' => 1, 'year' => 2018, 'period' => '2018-1', 'periodCode' => '2018-01', 'periodLabel' => 'janvier 2018' ],
            ['date' => '2018-02', 'month' => 2, 'year' => 2018, 'period' => '2018-2', 'periodCode' => '2018-02', 'periodLabel' => 'février 2018' ],
            ['date' => '2018-2', 'month' => 2, 'year' => 2018, 'period' => '2018-2', 'periodCode' => '2018-02', 'periodLabel' => 'février 2018' ],
            ['date' => '2018-12', 'month' => 12, 'year' => 2018, 'period' => '2018-12', 'periodCode' => '2018-12', 'periodLabel' => 'décembre 2018' ],
        ];
        foreach ($valid as $dt){
            $result = DateTimeUtils::extractPeriodDatasFromString($dt['date']);

            $this->assertEquals(5, count($result));
            $this->assertEquals($dt['month'], $result['month'], 'Le mois extrait ne correspond pas pour ' . $dt['date']);
            $this->assertEquals($dt['year'], $result['year'], 'Année extrait ne correspond pas pour ' . $dt['date']);
            $this->assertEquals($dt['period'], $result['period'], 'Année extrait ne correspond pas pour ' . $dt['date']);
            $this->assertEquals($dt['periodCode'], $result['periodCode'], 'Année extrait ne correspond pas pour ' . $dt['date']);
            $this->assertEquals($dt['periodLabel'], $result['periodLabel'], 'Année extrait ne correspond pas pour ' . $dt['date']);
        }
    }

    public function testGetCodePeriod(){
        $this->assertEquals('2018-01', DateTimeUtils::getCodePeriod('2018', '1'));
        $this->assertEquals('2017-12', DateTimeUtils::getCodePeriod('2017', '12'));
    }

    public function testAllperiodsBetweenTwo(){
        $periods = DateTimeUtils::allperiodsBetweenTwo('2018-01', '2018-12');
        $this->assertEquals(12, count($periods));
        $this->assertEquals('2018-01', $periods[0]);
        $this->assertEquals('2018-12', $periods[11]);

        $periods = DateTimeUtils::allperiodsBetweenTwo('2018-06', '2019-06');
        $this->assertEquals(13, count($periods));
        $this->assertEquals('2018-06', $periods[0]);
        $this->assertEquals('2019-06', $periods[12]);
    }



    public function testSDateKO(){


        $bad = [
            ['date' => '2018-13', 'month' => 12, 'year' => 2018 ],
            ['date' => '2018-0', 'month' => 1, 'year' => 2018 ],
        ];

        foreach ($bad as $dt){
            try {
                $result = DateTimeUtils::extractPeriodDatasFromString($dt['date']);
                $this->fail('Doit lever une exception');
            } catch (OscarException $e ) {
                $this->assertTrue(true);
                continue;
            } catch (\Exception $e ){
                $this->fail("Doit lever une Oscar Exception pour " . $dt['date'] . " > " . $e->getMessage());
            }
        }
    }
}