<?php

namespace UnicaenAuthTest\Options;

use UnicaenAppTest\BaseServiceFactoryTest;
use Zend\Config\Config;

/**
 * Description of ModuleOptionsFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ModuleOptionsFactoryTest extends BaseServiceFactoryTest
{
    protected $factoryClass = 'UnicaenAuth\Options\ModuleOptionsFactory';
    protected $serviceClass = 'UnicaenAuth\Options\ModuleOptions';

    public function testCanCreateServiceWithoutOptions()
    {
        $config = [
            'zfcuser'      => [],
            'unicaen-auth' => [],
        ];

        $this->serviceManager->expects($this->once())
                ->method('get')
                ->with('Configuration')
                ->will($this->returnValue($config));

        $service = $this->factory->createService($this->serviceManager);

        $this->assertInstanceOf($this->serviceClass, $service);
    }

    public function testCanCreateServiceWithOptions()
    {
        $config = [
            'zfcuser' => [
                'login_redirect_route'  => 'login',
                'logout_redirect_route' => 'home',
            ],
            'unicaen-auth' => [
                'login_redirect_route'       => 'other',
                'save_ldap_user_in_database' => true,
            ],
        ];

        $this->serviceManager->expects($this->once())
                ->method('get')
                ->with('Configuration')
                ->will($this->returnValue($config));

        $service = $this->factory->createService($this->serviceManager); /* @var $service \UnicaenAuth\Options\ModuleOptions */

        $this->assertInstanceOf($this->serviceClass, $service);
        $this->assertEquals('other', $service->getLoginRedirectRoute());
        $this->assertEquals('home', $service->getLogoutRedirectRoute());
        $this->assertEquals(true, $service->getSaveLdapUserInDatabase());
    }
}