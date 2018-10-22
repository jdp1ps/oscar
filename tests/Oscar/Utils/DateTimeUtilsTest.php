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
            ['date' => '2018-01', 'month' => 1, 'year' => 2018 ],
            ['date' => '2018-1', 'month' => 1, 'year' => 2018 ],
            ['date' => '2018-02', 'month' => 2, 'year' => 2018 ],
            ['date' => '2018-2', 'month' => 2, 'year' => 2018 ],
            ['date' => '2018-12', 'month' => 12, 'year' => 2018 ],
        ];

        foreach ($valid as $dt){
            $result = DateTimeUtils::extractPeriodDatasFromString($dt['date']);

            $this->assertEquals(3, count($result));
            $this->assertEquals($dt['month'], $result['month'], 'Le mois extrait ne correspond pas pour ' . $dt['date']);
            $this->assertEquals($dt['year'], $result['year'], 'Année extrait ne correspond pas pour ' . $dt['date']);
        }
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