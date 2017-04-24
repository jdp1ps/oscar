<?php

namespace UnicaenAuthTest\Provider\Role;

/**
 * Description of DbServiceFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class DbServiceFactoryTest extends BaseServiceFactoryTest
{
    protected $factoryClass = 'UnicaenAuth\Provider\Role\DbRoleServiceFactory';
    protected $serviceClass = 'UnicaenAuth\Provider\Role\DbRole';

    public function provideInvalidOptions()
    {
        return [
            'a' => [[]],
            'b' => [['role_providers']],
            'c' => [['role_providers' => []]],
            'd' => [['role_providers' => ['UnicaenAuth\Provider\Role\DbRole' => []]]],
            'e' => [['role_providers' => ['UnicaenAuth\Provider\Role\DbRole' => ['role_entity_class' => null]]]],
            'f' => [['role_providers' => ['UnicaenAuth\Provider\Role\DbRole' => ['role_entity_class' => 'A', 'object_manager' => null]]]],
        ];
    }

    /**
     * @dataProvider provideInvalidOptions
     * @expectedException \BjyAuthorize\Exception\InvalidArgumentException
     */
    public function testCannotCreateServiceWithInvalidOptions($options)
    {
        $this->serviceLocator->expects($this->once())
                ->method('get')
                ->with('BjyAuthorize\Config')
                ->will($this->returnValue($options));

        $this->factory->createService($this->serviceLocator);
    }

    public function testCanCreateService()
    {
        $options = [
            'role_providers' => [
                'UnicaenAuth\Provider\Role\DbRole' => [
                    'role_entity_class' => 'Entity',
                    'object_manager'    => 'orm_default',
                ]
            ]
        ];

        $objectManager = $this->getMockForAbstractClass('Doctrine\Common\Persistence\ObjectManager', ['getRepository']);
        $objectManager->expects($this->once())
                ->method('getRepository')
                ->will($this->returnValue($this->getMock('Doctrine\Common\Persistence\ObjectRepository', [])));

        $map = [
            ['BjyAuthorize\Config', $options],
            ['orm_default',         $objectManager],
        ];
        $this->serviceLocator->expects($this->exactly(2))
                ->method('get')
                ->will($this->returnValueMap($map));

        $service = $this->factory->createService($this->serviceLocator);

        $this->assertInstanceOf('UnicaenAuth\Provider\Role\DbRole', $service);
    }
}