<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 13/11/15 15:34
 * @copyright Certic (c) 2015
 */

namespace OscarTest\Entity;


use Oscar\Entity\ActivityPerson;

class TraitRoleTest extends \PHPUnit_Framework_TestCase
{
    public function testIsObsolete()
    {
        // VIEUX (début et fin fixe)
        $role = new ActivityPerson();
        $role->setDateStart(new \DateTime('2014-01-01'))
            ->setDateEnd(new \DateTime('2015-01-01'));
        $this->assertTrue($role->isOutOfDate(), "Passé, début et fin fixe");
        $this->assertTrue($role->isPast(), "Passé, début et fin fixe");
        $this->assertFalse($role->isFuture(), "Passé, début et fin fixe");

        // VIEUX (fin fixe)
        $role = new ActivityPerson();
        $role->setDateEnd(new \DateTime(((int)date('Y')-1).'-01-01'));
        $this->assertTrue($role->isOutOfDate(), "Passé, fin fixe");
        $this->assertTrue($role->isPast(), "Passé, fin fixe");
        $this->assertFalse($role->isFuture(), "Passé, fin fixe");

        // Prochainement (début fixe)
        $role = new ActivityPerson();
        $role->setDateStart(new \DateTime(((int)date('Y')+1).'-01-01'));
        $this->assertTrue($role->isOutOfDate(), "Future, début fixe");
        $this->assertTrue($role->isFuture());
        $this->assertFalse($role->isPast());

        // Prochainement (début et fin fixe)
        $role = new ActivityPerson();
        $role->setDateStart(new \DateTime(((int)date('Y')+1).'-01-01'))
            ->setDateEnd(new \DateTime(((int)date('Y')+2).'-01-01'));
        $this->assertTrue($role->isOutOfDate(), "Future, début et fin fixe");
        $this->assertTrue($role->isFuture());
        $this->assertFalse($role->isPast());

        // Actif (début fix)
        $role = new ActivityPerson();
        $role->setDateStart(new \DateTime(((int)date('Y')-1).'-01-01'));
        $this->assertFalse($role->isOutOfDate(), "Actif, début fixe");
        $this->assertFalse($role->isFuture());
        $this->assertFalse($role->isPast());

        // Actif (fin fixe)
        $role = new ActivityPerson();
        $role->setDateEnd(new \DateTime(((int)date('Y')+1).'-01-01'));
        $this->assertFalse($role->isOutOfDate(), "Actif, fin fixe");
        $this->assertFalse($role->isFuture());
        $this->assertFalse($role->isPast());

        // infini
        $role = new ActivityPerson();
        $this->assertFalse($role->isOutOfDate(), "Actif, sans date (infini)");
        $this->assertFalse($role->isFuture());
        $this->assertFalse($role->isPast());
    }

}