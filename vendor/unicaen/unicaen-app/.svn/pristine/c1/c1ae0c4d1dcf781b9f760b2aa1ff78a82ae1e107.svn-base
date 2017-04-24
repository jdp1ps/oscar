<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 23/07/15
 * Time: 14:29
 */

namespace UnicaenAppTest\Message;

use UnicaenApp\Message\MessageConfigNormalizer;
use UnicaenApp\Message\Specification\IsEqualSpecification;

class MessageConfigNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceLocator;

    /**
     * @var MessageConfigNormalizer
     */
    private $normalizer;

    protected function setUp()
    {
        $this->serviceLocator = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface');
        $this->normalizer     = new MessageConfigNormalizer($this->serviceLocator);
    }

    public function testSettingNewConfigWillForceNormalizationToBeReprocessed()
    {
        $this->normalizer->setConfig(['messages' => []]);
        $normalized = $this->getObjectAttribute($this->normalizer, 'normalized');
        $this->assertFalse($normalized);

        $this->normalizer->getNormalizedConfig();
        $normalized = $this->getObjectAttribute($this->normalizer, 'normalized');
        $this->assertTrue($normalized);

        $this->normalizer->setConfig(['messages' => []]);
        $normalized = $this->getObjectAttribute($this->normalizer, 'normalized');
        $this->assertFalse($normalized);
    }

    /**
     * @expectedException \UnicaenApp\Message\Exception\ConfigException
     */
    public function testThatNormalizingNullConfigThrowsException()
    {
        $this->normalizer
            ->setConfig(null)
            ->getNormalizedConfig();
    }

    /**
     * @expectedException \UnicaenApp\Message\Exception\ConfigException
     */
    public function testThatNormalizingNonArrayConfigThrowsException()
    {
        $this->normalizer
            ->setConfig('not an array')
            ->getNormalizedConfig();
    }

    public function testCanNormalizeEmptyConfig()
    {
        $normalizedConfig = $this->normalizer
            ->setConfig([])
            ->getNormalizedConfig();

        $this->assertEquals(['messages' => []], $normalizedConfig);
    }

    public function getInvalidConfigs()
    {
        return [
            [
                [
                    'messages' => 'not an array !'
                ],
            ],
            [
                [
                    'messages' => [
                        [
                            // pas de clé 'id'
                            'data' => [
                                // peu importe le contenu
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'messages' => [
                        [
                            'id' => 'VALID_ID',
                            // pas de clé 'data'
                        ],
                    ],
                ],
            ],
            [
                [
                    'messages' => [
                        [
                            'id'   => null,
                            'data' => [
                                // peu importe le contenu
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'messages' => [
                        [
                            'id'   => 12,
                            'data' => [
                                // peu importe le contenu
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'messages' => [
                        [
                            'id'   => 'VALID_ID',
                            'data' => [
                                // empty data !
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'messages' => [
                        [
                            'id'   => 'VALID_ID',
                            'data' => new \ArrayObject([
                                // peu importe le contenu
                            ]),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getInvalidConfigs
     * @expectedException \UnicaenApp\Message\Exception\ConfigException
     * @param array $config
     */
    public function testCannotNormalizeInvalidConfig($config)
    {
        $this->normalizer
            ->setConfig($config)
            ->getNormalizedConfig();
    }

    public function getConfigWithSpecificationsToBePreservedByNormalization()
    {
        return [
            [
                true
            ],
            [
                function($context) { return true; }
            ],
            [
                new IsEqualSpecification(12)
            ],
        ];
    }

    /**
     * @dataProvider getConfigWithSpecificationsToBePreservedByNormalization
     * @param $specificationToPreserve
     */
    public function testNormalizingConfigPreservesSomeSpecification($specificationToPreserve)
    {
        $config = [
            'messages' => [
                [
                    'id' => 'ID_1',
                    'data' => [
                        "Texte 1" => $specificationToPreserve,
                    ],
                ],
            ],
        ];

        $normalizedConfig = $this->normalizer
            ->setConfig($config)
            ->getNormalizedConfig();

        $normalizedSpecification = $normalizedConfig['messages'][0]['data']["Texte 1"];

        $this->assertEquals($specificationToPreserve, $normalizedSpecification);
    }

    public function testCanCreateServiceWithSpecificationBeingAServiceName()
    {
        $specificationServiceName = 'specification_service_name';

        $config = [
            'messages' => [
                [
                    'id' => 'ID_1',
                    'data' => [
                        "Texte 1" => $specificationServiceName,
                    ],
                ],
            ],
        ];

        $specificationService = $this->getMockForAbstractClass('UnicaenApp\Message\Specification\MessageSpecificationInterface');

        $this->serviceLocator
            ->method('has')
            ->with($specificationServiceName)
            ->willReturn(true);
        $this->serviceLocator
            ->method('get')
            ->with($specificationServiceName)
            ->willReturn($specificationService);

        $normalizedConfig = $this->normalizer
            ->setConfig($config)
            ->getNormalizedConfig();

        $substitutedSpecification = $normalizedConfig['messages'][0]['data']["Texte 1"];

        $this->assertEquals($specificationService, $substitutedSpecification);
    }

    public function testThatStringSpecificationNotFoundAsAServiceIsReplacedByIsEqualSpecification()
    {
        $unknownSpecificationServiceName = 'unknown_service_name';

        $config = [
            'messages' => [
                [
                    'id' => 'ID_1',
                    'data' => [
                        "Texte 1" => $unknownSpecificationServiceName,
                    ],
                ],
            ],
        ];

        $this->serviceLocator
            ->method('has')
            ->with($unknownSpecificationServiceName)
            ->willReturn(false); // unknown service name

        $normalizedConfig = $this->normalizer
            ->setConfig($config)
            ->getNormalizedConfig();

        $isEqualSpecification = $normalizedConfig['messages'][0]['data']["Texte 1"];

        $this->assertInstanceOf(get_class(new IsEqualSpecification('')), $isEqualSpecification);
        $this->assertEquals($unknownSpecificationServiceName, $isEqualSpecification->getValue());
    }

    public function testThatNormalizingConfigWithOtherSpecificationTypeReplacesSpecificationByIsEqualSpecification()
    {
        $config = [
            'messages' => [
                [
                    'id' => 'ID_1',
                    'data' => [
                        "Texte 1" => 2015,
                    ],
                ],
            ],
        ];

        $normalizedConfig = $this->normalizer
            ->setConfig($config)
            ->getNormalizedConfig();

        $isEqualSpecification = $normalizedConfig['messages'][0]['data']["Texte 1"]; /** @var $isEqualSpecification IsEqualSpecification */

        $this->assertInstanceOf(get_class(new IsEqualSpecification('')), $isEqualSpecification);
        $this->assertEquals(2015, $isEqualSpecification->getValue());
    }
}
