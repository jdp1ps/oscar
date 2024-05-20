<?php

use PHPUnit\Framework\TestCase;


class JsonToPersonFactoryTest extends TestCase {
    public function testGetInstance(){

        $data = [
            "firstname" => "Stéphane",
            "lastname" => "Bouvry",
        ];
        $factory = new \Oscar\Factory\JsonToPersonFactory();

        try {
            /** @var \Oscar\Entity\Person $person */
            $person = $factory->getInstance((object) $data);
            throw new Exception("Devrez lever une exception, UID manquant");
        } catch (\Oscar\Exception\OscarException $e ){
            // OK
        }

        $data['uid'] = "p0ed329";
        $person = $factory->getInstance((object) $data);
        $this->assertEquals("Stéphane",     $person->getFirstname());
        $this->assertEquals("Bouvry",     $person->getLastname());

        $data['uid'] = "p0ed329";

        $data['groups'] = null;
        $data['structure'] = "structure";
        $data['affectation'] = "affectation";
        $data['status'] = "contractuel";
        $data['phone'] = "+330686308337";
        $data['inm'] = "42";
        $data['mail'] = "stephane.bouvry@unicaen.fr";

        $person = $factory->getInstance((object) $data);
        $this->assertEquals("Stéphane",     $person->getFirstname());
        $this->assertEquals("Bouvry",     $person->getLastname());
        $this->assertEquals("structure",     $person->getLdapSiteLocation());
        $this->assertEquals("affectation",     $person->getLdapAffectation());
        $this->assertEquals("structure",     $person->getLdapSiteLocation());
        $this->assertEquals("contractuel",     $person->getLdapStatus());
        $this->assertEquals("+330686308337",     $person->getPhone());
        $this->assertEquals("stephane.bouvry@unicaen.fr",     $person->getEmail());
    }
}
