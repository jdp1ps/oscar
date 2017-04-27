<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 23/07/15
 * Time: 17:52
 */

namespace UnicaenAppTest\Message\Specification;

use UnicaenApp\Message\Specification\IsEqualSpecification;

class IsEqualSpecificationTest extends \PHPUnit_Framework_TestCase
{
    public function getDataset()
    {
        return [
            [
                'value',
                'value',
                true,
            ],
            [
                'value',
                'other value',
                false
            ],
            [
                12,
                12,
                true
            ],
            [
                12,
                12.0,
                false
            ],
            [
                1,
                true,
                false
            ],
            [
                ['role' => 'admin'],
                ['role' => 'admin'],
                true
            ],
            [
                ['role' => 'admin'],
                ['role' => 'guest'],
                false
            ],
        ];
    }

    /**
     * @dataProvider getDataset
     * @param mixed $value
     * @param mixed $context
     * @param bool $expectedSatisfaction
     */
    public function testIsSatisfiedByMethod($value, $context, $expectedSatisfaction)
    {
        $spec = new IsEqualSpecification($value);

        $this->assertEquals($expectedSatisfaction, $spec->isSatisfiedBy($context));
    }
}
