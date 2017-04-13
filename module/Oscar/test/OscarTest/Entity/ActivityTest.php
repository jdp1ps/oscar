<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 06/11/15 14:31
 * @copyright Certic (c) 2015
 */
class ActivityTest extends PHPUnit_Framework_TestCase
{
    public function testHasPerson1()
    {
        $person = new \Oscar\Entity\Person();
        $notIn = new \Oscar\Entity\Person();

        $activity = new \Oscar\Entity\Activity();

        $jc = new \Oscar\Entity\ActivityPerson();
        $jc->setPerson($person)->setActivity($activity)->setRole("Régis");

        $activity->getPersons()->add($jc);

        $this->assertTrue($activity->hasPerson($person), 'Doit être TRUE');
        $this->assertTrue($activity->hasPerson($person, "Régis"), 'Doit être TRUE');

        $this->assertFalse(!$activity->hasPerson($notIn), 'Doit être FALSE');

    }
}