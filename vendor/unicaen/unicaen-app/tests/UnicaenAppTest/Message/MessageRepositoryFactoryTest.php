<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 22/07/15
 * Time: 14:06
 */

namespace UnicaenAppTest\Message;

use UnicaenApp\Message\MessageRepositoryFactory;
use UnicaenAppTest\Message\Specification\ByRoleSpecificationTestAsset;

class MessageRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceLocator;

    protected function setUp()
    {
        $this->serviceLocator = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface');
    }

//    public function testCanCreateServiceWithEmptyConfig()
//    {
//        $this->givenThatServiceLocatorWillReturnThisMessageConfig([]);
//
//        $factory = new MessageRepositoryFactory();
//        $service = $factory->createService($this->serviceLocator);
//        $this->assertInstanceOf('UnicaenApp\Message\MessageRepository', $service);
//    }
//
//    public function getInvalidConfigs()
//    {
//        return [
//            [
//                [
//                    'message' => 'not an array !',
//                ],
//            ],
//            [
//                [
//                    'message' => [],
//                ],
//            ],
//            [
//                [
//                    'message' => [
//                        'messages' => 'not an array !'
//                    ],
//                ],
//            ],
//            [
//                [
//                    'message' => [
//                        'messages' => [
//                            [
//                                // pas de clé 'id'
//                                'data' => [
//                                    // peu importe le contenu
//                                ],
//                            ],
//                        ],
//                    ],
//                ],
//            ],
//            [
//                [
//                            [
//                                'id' => 'VALID_ID',
//                                // pas de clé 'data'
//                            ],
//                ],
//            ],
//            [
//                [
//                            [
//                                'id' => null,
//                                'data' => [
//                                    // peu importe le contenu
//                                ],
//                            ],
//                ],
//            ],
//            [
//                [
//                            [
//                                'id' => 12,
//                                'data' => [
//                                    // peu importe le contenu
//                                ],
//                            ],
//                ],
//            ],
//            [
//                [
//                            [
//                                'id' => 'VALID_ID',
//                                'data' => [
//                                    // empty data !
//                                ],
//                            ],
//                ],
//            ],
//            [
//                [
//                            [
//                                'id' => 'VALID_ID',
//                                'data' => new \ArrayObject([
//                                    // peu importe le contenu
//                                ]),
//                            ],
//                ],
//            ],
//        ];
//    }
//
//    /**
//     * @dataProvider getInvalidConfigs
//     * @expectedException \UnicaenApp\Message\Exception\ConfigException
//     * @param array $config
//     */
//    public function testCannotCreateServiceWithInvalidConfig($config)
//    {
//        $this->givenThatServiceLocatorWillReturnThisMessageConfig($config);
//
//        $factory = new MessageRepositoryFactory();
//        $factory->createService($this->serviceLocator);
//    }

    public function testCanCreateService()
    {
        $config = [
            [
                'id' => 'ID_1',
                'data' => [
                    "Les données personnelles de {intervenant} ont été saisies." => true,
                ],
            ],
        ];
        $this->givenThatServiceLocatorWillReturnThisMessageConfig($config);

        $factory = new MessageRepositoryFactory();
        $service = $factory->createService($this->serviceLocator);
        $this->assertInstanceOf('UnicaenApp\Message\MessageRepository', $service);
    }
//
//    public function testThatUnknownServiceSpecificationIsNotAProblem()
//    {
//        $specificationServiceName = 'unknown_service_name';
//
//        $config = [
//            [
//                'id' => 'ID_1',
//                'data' => [
//                    "Les données personnelles de {intervenant} ont été saisies." => $specificationServiceName,
//                ],
//            ],
//        ];
//        $this->givenThatServiceLocatorWillReturnThisMessageConfig($config);
//
//        $this->serviceLocator
//            ->method('has')
//            ->with($specificationServiceName)
//            ->willReturn(false); // unknown service
//
//        $factory = new MessageRepositoryFactory();
//        $service = $factory->createService($this->serviceLocator);
//        $this->assertInstanceOf('UnicaenApp\Message\MessageRepository', $service);
//    }
//
//    public function testCanCreateServiceWithSpecificationBeingAServiceName()
//    {
//        $specificationServiceName = 'specification_service_name';
//
//        $config = [
//            [
//                'id' => 'ID_123',
//                'data' => [
//                    "Les données personnelles de {intervenant} ont été saisies." => $specificationServiceName,
//                ],
//            ],
//        ];
//        $messageConfig = $this->newMessageConfigMock($config);
//
//        $this->serviceLocator
//            ->method('has')
//            ->with($specificationServiceName)
//            ->willReturn(true);
//        $map = [
//            ['MessageConfig', $messageConfig],
//            [$specificationServiceName, new ByRoleSpecificationTestAsset('admin')],
//        ];
//        $this->serviceLocator
//            ->method('get')
//            ->will($this->returnValueMap($map));
//
//        $factory = new MessageRepositoryFactory();
//        $service = $factory->createService($this->serviceLocator);
//        $this->assertInstanceOf('UnicaenApp\Message\MessageRepository', $service);
//    }

    private function givenThatServiceLocatorWillReturnThisMessageConfig($config)
    {
        $messageConfig = $this->newMessageConfigMock($config);

        $this->serviceLocator
            ->method('get')
            ->with('MessageConfig')
            ->willReturn($messageConfig);

        return $this;
    }

    private function newMessageConfigMock($config)
    {
        $messageConfig = $this->getMockBuilder('UnicaenApp\Message\MessageConfig')
            ->disableOriginalConstructor()
            ->setMethods(['getMessagesConfig'])
            ->getMock();
        $messageConfig
            ->method('getMessagesConfig')
            ->willReturn($config);

        return $messageConfig;
    }
}
