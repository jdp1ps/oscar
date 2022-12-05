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

class DateTimeUtilsTest extends TestCase
{
    public function testNormalize(){
        $datas = [
            '1-2021' => '01-2021',
            '2-2021' => '02-2021',
            '3-2021' => '03-2021',
            '4-2021' => '04-2021',
            '5-2021' => '05-2021',
            '6-2021' => '06-2021',
            '7-2021' => '07-2021',
            '8-2021' => '08-2021',
            '9-2021' => '09-2021',
            '10-2021' => '10-2021',
            '11-2021' => '11-2021',
            '12-2021' => '12-2021',
        ];
        foreach ($datas as $entry=>$expected) {
            try {
                $out = DateTimeUtils::normalizePeriodStr($entry);
            } catch (\Exception $e) {
                $out = $e->getMessage();
            }
            $this->assertEquals($out, $expected);
        }
    }

    public function testNormalizeError(){
        $datas = [
            '0-2021' => "La période '0-2021' est invalide",
            '13-2021' => "La période '13-2021' est invalide",
        ];
        foreach ($datas as $entry=>$expected) {
            try {
                $out = DateTimeUtils::normalizePeriodStr($entry);
            } catch (\Exception $e) {
                $out = $e->getMessage();
            }
            $this->assertEquals($out, $expected);
        }
    }

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

    public function testAllPeriodsFromDates(){
        $periods = DateTimeUtils::allPeriodsFromDates(['2018-01', '2018-06'],['2019-01', '2019-02'], ['2018-03', '2018-07']);
        $this->assertEquals(9, count($periods));
        $this->assertEquals('2018-01', $periods[0]);
        $this->assertEquals('2018-02', $periods[1]);
        $this->assertEquals('2018-03', $periods[2]);
        $this->assertEquals('2018-04', $periods[3]);
        $this->assertEquals('2018-05', $periods[4]);
        $this->assertEquals('2018-06', $periods[5]);
        $this->assertEquals('2018-07', $periods[6]);
        $this->assertEquals('2019-01', $periods[7]);
        $this->assertEquals('2019-02', $periods[8]);

    }

    public function testPeriodInside(){

        $period = '2019-07';
        $this->assertTrue(DateTimeUtils::periodInside('2019-07', new \DateTime('2019-01-01'), new \DateTime('2019-12-31')))  ;
        $this->assertTrue(DateTimeUtils::periodInside('2019-01', new \DateTime('2019-01-01'), new \DateTime('2019-01-31')))  ;
        $this->assertFalse(DateTimeUtils::periodInside('2019-07', new \DateTime('2020-01-01'), new \DateTime('2020-12-31')))  ;
       // $this->assertTrue(DateTimeUtils::periodInside('2019-07', new \DateTime('2019-01-01'), new \DateTime('2019-12-31')))  ;
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