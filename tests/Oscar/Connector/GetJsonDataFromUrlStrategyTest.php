<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-17 10:17
 * @copyright Certic (c) 2017
 */

use PHPUnit\Framework\TestCase;

class GetJsonDataFromUrlStrategyTest extends TestCase
{
    public function testBadURL(){
        $urlAll = 'http://no-right-url';
        $urlOne = $urlAll.'/%s';

        $jsonDataReader = new \Oscar\Connector\GetJsonDataFromUrlStrategy($urlOne, $urlAll);

        try {
            $jsonDataReader->getAll();
            $this->fail("Un mauvaise URL devrait lever une exception");
        }
        catch (\Oscar\Exception\OscarException $e ){
            $this->assertEquals(true, true);
        }
    }
}
