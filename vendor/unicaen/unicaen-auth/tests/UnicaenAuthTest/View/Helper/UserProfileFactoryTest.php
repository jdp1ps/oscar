<?php

namespace UnicaenAppTest\View\Helper;

use UnicaenAppTest\View\Helper\BaseServiceFactoryTest;

/**
 * Description of UserProfileFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserProfileFactoryTest extends BaseServiceFactoryTest
{
    protected $factoryClass = 'UnicaenAuth\View\Helper\UserProfileFactory';
    protected $serviceClass = 'UnicaenAuth\View\Helper\UserProfile';

    public function testCanCreateService()
    {
        $authService      = $this->getMock('Zend\Authentication\AuthenticationService', []);
        $authorize        = $this->getMock('BjyAuthorize\Service\Authorize', ['getIdentityProvider'], [], '', false);
        $identityProvider = $this->getMockForAbstractClass('BjyAuthorize\Provider\Identity\ProviderInterface', []);

        $authorize->expects($this->once())
                ->method('getIdentityProvider')
                ->will($this->returnValue($identityProvider));

        $this->serviceManager->expects($this->exactly(2))
                ->method('get')
                ->will($this->returnValueMap([
                    ['zfcuser_auth_service', $authService],
                    ['BjyAuthorize\Service\Authorize', $authorize]]));

        $service = $this->factory->createService($this->pluginManager);

        $this->assertInstanceOf($this->serviceClass, $service);
        $this->assertSame($identityProvider, $service->getIdentityProvider());
    }
}