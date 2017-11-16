<?php

use PHPUnit\Framework\TestCase;


class JsonToPersonFactoryTest extends TestCase {
    public function testGetInstance(){

        $data = [
            "firstname" => "Stéphane",
            "lastname" => "Bouvry",
        ];
        $factory = new \Oscar\Factory\JsonToPersonFactory();
        $person = $factory->getInstance((object) $data);
        $this->assertEquals("Stéphane",     $person->getFirstname());
    }
}
