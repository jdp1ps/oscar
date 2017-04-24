<?php

namespace UnicaenAuthTest\Provider\Identity;

use PHPUnit_Framework_TestCase;

/**
 * Description of BaseServiceFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
abstract class BaseServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $serviceLocator;
    protected $userService;
    protected $factory;
    protected $factoryClass;
    protected $serviceClass;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->factory        = new $this->factoryClass();
        $this->serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface', []);
        $this->authService    = $this->getMock('Zend\Authentication\AuthenticationService', []);
        $this->userService    = $this->getMock('ZfcUser\Service\User', ['getAuthService']);
    }

    public function testCanCreateService()
    {
        $this->userService->expects($this->once())
                ->method('getAuthService')
                ->will($this->returnValue($this->authService));

        $map = [
            ['zfcuser_user_service', $this->userService],
            ['BjyAuthorize\Config',  ['default_role' => 'default', 'authenticated_role' => 'auth']],
        ];
        $this->serviceLocator->expects($this->any())
                ->method('get')
                ->will($this->returnValueMap($map));

        $service = $this->factory->createService($this->serviceLocator);

        $this->assertInstanceOf($this->serviceClass, $service);
        $this->assertEquals('default', $service->getDefaultRole());
        $this->assertEquals('auth', $service->getAuthenticatedRole());
    }
}