<?php

use PHPUnit\Framework\TestCase;


class JsonToOrganizationFactoryTest extends TestCase {

    /**
     * @return \Oscar\Factory\JsonToOrganization
     */
    protected function getFactory(){
        static $factory;
        if( $factory === null )
            $factory = new \Oscar\Factory\JsonToOrganization([]);
        return $factory;
    }


    public function testGetInstance(){

        try {
            $organization = $this->getFactory()->getInstance((object) null,
                'GetJsonDataFromFileStrategyTest');
            throw PHPUnitException('Un objet sans les champs requis doit lever une exception');
        } catch (\Oscar\Exception\OscarException $e ){
            $this->assertTrue(True);
        }


        $organization = $this->getFactory()->getInstance((object) [
            'uid' => 'T1',
            'code' => 'ED209',
            'shortname' => 'Etude Directive 20 Al 9'],
            'GetJsonDataFromFileStrategyTest');

        $this->assertEquals('Etude Directive 20 Al 9', $organization->getShortName());
        $this->assertEquals('T1', $organization->getConnectorID('GetJsonDataFromFileStrategyTest'));
        $this->assertEquals('ED209', $organization->getCode());
        $this->assertNull( $organization->getConnectorID('unknown'), "Getter sur un connector absent retourne NULL");
    }

    public function testThrowMissingUid(){
        try {
            $organization = $this->getFactory()->getInstance((object) null,
                'GetJsonDataFromFileStrategyTest');
            throw PHPUnitException('Un objet sans les champs requis doit lever une exception');
        } catch (\Oscar\Exception\OscarException $e ){
            $this->assertTrue(True);
        }

    }
}
