<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-23 11:25
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import;


use Oscar\Entity\Activity;
use Oscar\Import\Activity\FieldStrategy\FieldImportSetterStrategy;
use PHPUnit\Framework\TestCase;

class FieldImportSetterStrategyTest extends TestCase
{
    public function testRun()
    {
        $activity = new Activity();
        $this->assertEquals("", $activity->getLabel());
        $setterStrategy = new FieldImportSetterStrategy('label');
        $setterStrategy->run($activity, ['TEST'], 0);
        $this->assertEquals($activity->getLabel(), "TEST");
    }
}