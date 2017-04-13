<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 23/11/15 11:01
 * @copyright Certic (c) 2015
 */

namespace OscarTest\Service;


use Oscar\Entity\ActivityType;
use Oscar\Service\ActivityTypeService;

class ActivityTypeServiceTest extends \PHPUnit_Framework_TestCase
{
    private function createActivity( $label, $l, $r)
    {
        $a = new ActivityType();
        return $a->setLabel($label)
            ->setLft($l)
            ->setRgt($r);
    }

    public function testToto()
    {
        $n1 = $this->createActivity('N1',1, 10);
        $n2 = $this->createActivity('N2',2, 5);
        $n5 = $this->createActivity('N5',3, 4);
        $n3 = $this->createActivity('N3',6, 7);
        $n4 = $this->createActivity('N4',8, 9);
        $tree = [$n1, $n2, $n3, $n4, $n5];




        //$this->assertFalse(true);
    }
}