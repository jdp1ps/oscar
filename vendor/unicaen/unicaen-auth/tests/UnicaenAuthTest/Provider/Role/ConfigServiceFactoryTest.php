<?php

namespace UnicaenAuthTest\Provider\Role;

/**
 * Description of ConfigServiceFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ConfigServiceFactoryTest extends BaseServiceFactoryTest
{
    protected $factoryClass = 'UnicaenAuth\Provider\Role\ConfigServiceFactory';
    protected $serviceClass = 'UnicaenAuth\Provider\Role\Config';
    protected $mapper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->mapper = $this->getMock('UnicaenApp\Mapper\Ldap\Group', []);
    }

    public function provideInvalidOptions()
    {
        return [
            'a' => [[]],
            'b' => [['role_providers']],
            'c' => [['role_providers' => []]],
            'd' => [['role_providers' => ['UnicaenAuth\Provider\Role\Config' => null]]],
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
                'UnicaenAuth\Provider\Role\Config' => [],
            ],
        ];
        $map = [
            ['BjyAuthorize\Config', $options],
            ['ldap_group_mapper',   $this->mapper],
        ];
        $this->serviceLocator->expects($this->exactly(2))
                ->method('get')
                ->will($this->returnValueMap($map));

        $service = $this->factory->createService($this->serviceLocator);

        $this->assertInstanceOf('UnicaenAuth\Provider\Role\Config', $service);
    }
}