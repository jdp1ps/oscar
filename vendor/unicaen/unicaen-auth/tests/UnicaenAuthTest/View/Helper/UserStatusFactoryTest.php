<?php

namespace UnicaenAppTest\View\Helper;

use UnicaenAppTest\View\Helper\BaseServiceFactoryTest;

/**
 * Description of UserStatusFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserStatusFactoryTest extends BaseServiceFactoryTest
{
    protected $factoryClass = 'UnicaenAuth\View\Helper\UserStatusFactory';
    protected $serviceClass = 'UnicaenAuth\View\Helper\UserStatus';

    public function testCanCreateService()
    {
        $authService = $this->getMock('Zend\Authentication\AuthenticationService', []);

        $this->serviceManager->expects($this->once())
                ->method('get')
                ->with('zfcuser_auth_service')
                ->will($this->returnValue($authService));

        $service = $this->factory->createService($this->pluginManager);

        $this->assertInstanceOf($this->serviceClass, $service);
    }
}