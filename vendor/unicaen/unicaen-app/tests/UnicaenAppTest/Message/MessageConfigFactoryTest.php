<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 22/07/15
 * Time: 14:06
 */

namespace UnicaenAppTest\Message;

use UnicaenApp\Message\MessageConfigFactory;

class MessageConfigFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceLocator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $normalizer;

    protected function setUp()
    {
        $this->serviceLocator = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface');

        $this->normalizer = $this->getMockBuilder('UnicaenApp\Message\MessageConfigNormalizer')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCanObtainAMessageConfigNormalizerByDefault()
    {
        $factory = new MessageConfigFactory();
        $factory->createService($this->serviceLocator);
        $normalizer = $factory->getMessageConfigNormalizer();

        $this->assertInstanceOf('UnicaenApp\Message\MessageConfigNormalizer', $normalizer);
        $this->assertEquals($this->serviceLocator, $this->getObjectAttribute($normalizer, 'serviceLocator'));
    }

    public function testCanSetADifferentMessageConfigNormalizer()
    {
        $factory = new MessageConfigFactory();
        $factory->setMessageConfigNormalizer($this->normalizer);

        $this->assertEquals($this->normalizer, $factory->getMessageConfigNormalizer());
    }

    public function testThatCreatingServiceWithEmptyConfig()
    {
        $this->givenThatServiceLocatorWillReturnThisAppConfig([]);

        $factory = new MessageConfigFactory();
        $service = $factory->createService($this->serviceLocator);
        $this->assertInstanceOf('UnicaenApp\Message\MessageConfig', $service);
    }

    public function getInvalidConfigs()
    {
        return [
            'non_array_in_message_key' => [
                [
                    'message' => 'not an array !',
                ],
            ],
            'non_array_in_messages_key' => [
                [
                    'message' => [
                        'messages' => 'not an array !'
                    ],
                ],
            ],
            'no_id_key' => [
                [
                    'message' => [
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
            ],
            'no_data_key' => [
                [
                    'message' => [
                        'messages' => [
                            [
                                'id' => 'VALID_ID',
                                // pas de clé 'data'
                            ],
                        ],
                    ],
                ],
            ],
            'null_in_id_key' => [
                [
                    'message' => [
                        'messages' => [
                            [
                                'id' => null,
                                'data' => [
                                    // peu importe le contenu
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'integer_in_id_key' => [
                [
                    'message' => [
                        'messages' => [
                            [
                                'id' => 12,
                                'data' => [
                                    // peu importe le contenu
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'empty_array_in_data_key' => [
                [
                    'message' => [
                        'messages' => [
                            [
                                'id' => 'VALID_ID',
                                'data' => [
                                    // empty data !
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'non_array_in_data_key' => [
                [
                    'message' => [
                        'messages' => [
                            [
                                'id' => 'VALID_ID',
                                'data' => new \ArrayObject([
                                    // peu importe le contenu
                                ]),
                            ],
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
    public function testCannotCreateServiceWithInvalidConfig($config)
    {
        $this->givenThatServiceLocatorWillReturnThisAppConfig($config);

        $factory = new MessageConfigFactory();
        $factory->createService($this->serviceLocator);
    }

    private function givenThatServiceLocatorWillReturnThisAppConfig($config)
    {
        $this->serviceLocator
            ->method('get')
            ->with('Config')
            ->willReturn($config);

        return $this;
    }

    public function testThatAppConfigIsReducedToTheMessageConfigKeyIfItExists()
    {
        $this->givenThatServiceLocatorWillReturnThisAppConfig(['message' => ['peu' => 'importe']]);

        $this->normalizer
            ->expects($this->once())
            ->method('setConfig')
            ->with(['peu' => 'importe'])
            ->willReturnSelf();
        $this->normalizer
            ->expects($this->once())
            ->method('getNormalizedConfig')
            ->willReturn([]);

        $factory = new MessageConfigFactory();
        $factory->setMessageConfigNormalizer($this->normalizer);
        $factory->createService($this->serviceLocator);
    }

    public function testCanCreateService()
    {
        $this->givenThatServiceLocatorWillReturnThisAppConfig(['message' => ['peu' => 'importe']]);

        $this->normalizer
            ->method('setConfig')
            ->with(['peu' => 'importe'])
            ->willReturnSelf();
        $this->normalizer
            ->expects($this->once())
            ->method('getNormalizedConfig')
            ->willReturn([]);

        $factory = new MessageConfigFactory();
        $factory->setMessageConfigNormalizer($this->normalizer);
        $service = $factory->createService($this->serviceLocator);

        $this->assertInstanceOf('UnicaenApp\Message\MessageConfig', $service);
    }
}
