<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 06/11/15 14:31
 * @copyright Certic (c) 2015
 */
class ActivityTest extends \PHPUnit\Framework\TestCase
{
    public function testHasPerson1()
    {
        $person = new \Oscar\Entity\Person();
        $person->setFirstname("John")->setLastname('Doe');

        $notIn = new \Oscar\Entity\Person();
        $notIn->setFirstname("Noth")->setLastname('Heins');

        $activity = new \Oscar\Entity\Activity();

        $jc = new \Oscar\Entity\ActivityPerson();
        $role = new \Oscar\Entity\Role();
        $role->setRoleId("Participant");
        $jc->setPerson($person)->setActivity($activity)->setRoleObj($role);

        $activity->getPersons()->add($jc);

        $this->assertTrue($activity->hasPerson($person), 'Doit être TRUE');
        $this->assertTrue($activity->hasPerson($person, "Participant"), "La personne est dans l'activité avec le rôle donné");

        $this->assertFalse($activity->hasPerson($person, "Autre Rôle"), "La personne est dans l'activité, mais pas avec le rôle demandé");
        $this->assertFalse($activity->hasPerson($notIn), "La person n'est pas dans l'activité");

    }
}