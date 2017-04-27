<?php

namespace UnicaenAppTest\View\Helper;

use UnicaenAppTest\View\Helper\BaseServiceFactoryTest;

/**
 * Description of UserInfoFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserInfoFactoryTest extends BaseServiceFactoryTest
{
    protected $factoryClass = 'UnicaenAuth\View\Helper\UserInfoFactory';
    protected $serviceClass = 'UnicaenAuth\View\Helper\UserInfo';

    public function testCanCreateService()
    {
        $authService = $this->getMock('Zend\Authentication\AuthenticationService', []);
        $mapper      = $this->getMock('UnicaenApp\Mapper\Ldap\Structure', []);

        $this->serviceManager->expects($this->exactly(2))
                ->method('get')
                ->will($this->returnValueMap([
                    ['zfcuser_auth_service', $authService],
                    ['ldap_structure_mapper', $mapper]]));

        $service = $this->factory->createService($this->pluginManager);

        $this->assertInstanceOf($this->serviceClass, $service);
        $this->assertSame($mapper, $service->getMapperStructure());
    }
}