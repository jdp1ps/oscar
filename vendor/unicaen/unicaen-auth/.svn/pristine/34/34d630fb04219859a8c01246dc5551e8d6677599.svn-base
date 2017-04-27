<?php
namespace UnicaenAuthTest\View\Helper;

use PHPUnit_Framework_TestCase;
use UnicaenAuth\View\Helper\UserAbstract;

/**
 * Description of AppConnectionTest
 *
 * @property UserAbstract $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserAbstractTest extends PHPUnit_Framework_TestCase
{
    protected $helper;
    protected $authService;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->authService = $this->getMock('Zend\Authentication\AuthenticationService', ['hasIdentity', 'getIdentity']);

        $this->helper = $this->getMockForAbstractClass('UnicaenAuth\View\Helper\UserAbstract');
        $this->helper->setAuthService($this->authService);
    }

    public function testCanConstructWithAuthServiceSpecified()
    {
        $helper = $this->getMockForAbstractClass('UnicaenAuth\View\Helper\UserAbstract', [$this->authService]);
        $this->assertSame($this->authService, $helper->getAuthService());
    }

    public function testCanSetAuthService()
    {
        $this->assertSame($this->authService, $this->helper->getAuthService());
    }

    public function testGettingIdentityReturnsNullIfNoAuthServiceAvailable()
    {
        $this->helper->setAuthService(null);
        $this->assertNull($this->helper->getAuthService());
        $this->assertNull($this->helper->getIdentity());
    }

    public function testGettingIdentityReturnsNullIfAuthServiceHasNoIdentity()
    {
        $this->authService->expects($this->once())
                          ->method('hasIdentity')
                          ->will($this->returnValue(false));
        $this->assertNull($this->helper->getIdentity());
    }

    public function testGettingIdentityReturnsAuthServiceIdentity()
    {
        $this->authService->expects($this->once())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->once())
                          ->method('getIdentity')
                          ->will($this->returnValue($identity = 'Auth Service Identity'));
        $this->assertEquals($identity, $this->helper->getIdentity());
    }

    public function provideValidArrayIdentity()
    {
        return [
            'db-only' => [
                ['db' => 'Db Identity'],
                'Db Identity',
            ],
            'ldap-only' => [
                ['ldap' => 'Ldap Identity'],
                'Ldap Identity',
            ],
            'db-ldap' => [
                ['db'   => 'Db Identity', 'ldap' => 'Ldap Identity'],
                'Ldap Identity',
            ],
        ];
    }

    /**
     * @dataProvider provideValidArrayIdentity
     * @param array $identity
     * @param string $expected
     */
    public function testGettingIdentityReturnsAuthServiceIdentityFromValidArrayIdentity($identity, $expected)
    {
        $this->authService->expects($this->once())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->once())
                          ->method('getIdentity')
                          ->will($this->returnValue($identity));
        $this->assertEquals($expected, $this->helper->getIdentity());
    }

    public function provideValidArrayIdentityWithPreferedKey()
    {
        return [
            'db-only-ldap-prefered' => [
                ['db' => 'Db Identity'],
                'ldap', // clé absente
                'Db Identity',
            ],
            'ldap-only-db-prefered' => [
                ['ldap' => 'Ldap Identity'],
                'db', // clé absente
                'Ldap Identity',
            ],
            'db-ldap-none-prefered' => [
                ['db' => 'Db Identity', 'ldap' => 'Ldap Identity'],
                null, // équivaut à 'ldap'
                'Ldap Identity',
            ],
            'db-ldap-ldap-prefered' => [
                ['db' => 'Db Identity', 'ldap' => 'Ldap Identity'],
                'ldap',
                'Ldap Identity',
            ],
            'db-ldap-db-prefered' => [
                ['db' => 'Db Identity', 'ldap' => 'Ldap Identity'],
                'db',
                'Db Identity',
            ],
        ];
    }

    /**
     * @dataProvider provideValidArrayIdentityWithPreferedKey
     * @param array $identity
     * @param string $preferedKey
     * @param string $expected
     */
    public function testGettingIdentityReturnsAuthServiceIdentityFromValidArrayIdentityWithPreferedKey($identity, $preferedKey, $expected)
    {
        $this->authService->expects($this->once())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->once())
                          ->method('getIdentity')
                          ->will($this->returnValue($identity));
        $this->assertEquals($expected, $this->helper->getIdentity($preferedKey));
    }

    public function provideInvalidArrayIdentity()
    {
        return [
            [[]],
            [['other' => 'Other Identity']],
        ];
    }

    /**
     * @dataProvider provideInvalidArrayIdentity
     * @expectedException \InvalidArgumentException
     * @param array $identity
     */
    public function testGettingIdentityThrowsExceptionFromInvalidArrayIdentity($identity)
    {
        $this->authService->expects($this->once())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->once())
                          ->method('getIdentity')
                          ->will($this->returnValue($identity));
        $this->helper->getIdentity();
    }
}