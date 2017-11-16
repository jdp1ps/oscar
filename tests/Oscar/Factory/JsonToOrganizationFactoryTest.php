<?php

use PHPUnit\Framework\TestCase;


class JsonToOrganizationFactoryTest extends TestCase {

    /**
     * @return \Oscar\Factory\JsonToOrganizationFactory
     */
    protected function getFactory(){
        static $factory;
        if( $factory === null )
            $factory = new \Oscar\Factory\JsonToOrganizationFactory();
        return $factory;
    }


    public function testGetInstance(){

        try {
            $organization = $this->getFactory()->getInstance((object) null, 'test');
            throw PHPUnitException('Un objet sans les champs requis doit lever une exception');
        } catch (\Oscar\Exception\OscarException $e ){
            $this->assertTrue(True);
        }


        $organization = $this->getFactory()->getInstance((object) [
            'code' => 'ED209',
            'shortname' => 'Etude Directive 20 Al 9'], 'test');

        $this->assertEquals('Etude Directive 20 Al 9', $organization->getShortName());
        $this->assertEquals('ED209', $organization->getConnectorID('test'));
        $this->assertEquals('ED209', $organization->getCode());
        $this->assertNull( $organization->getConnectorID('unknown'), "Getter sur un connector absent retourne NULL");
    }
}
