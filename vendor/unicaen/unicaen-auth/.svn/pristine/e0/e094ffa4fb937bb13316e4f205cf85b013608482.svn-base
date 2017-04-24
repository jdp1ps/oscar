<?php
namespace UnicaenAuthTest\Authentication\Adapter;

use PHPUnit_Framework_TestCase;
use UnicaenAuth\Authentication\Adapter\Ldap;
use Zend\Authentication\Result;
use Zend\EventManager\EventManager;
use Zend\Http\Request;
use Zend\Stdlib\Parameters;
use ZfcUser\Authentication\Adapter\AdapterChainEvent;

/**
 * Description of LdapTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class LdapTest extends PHPUnit_Framework_TestCase
{
    protected $adapter;
    protected $appModuleOptions;
    protected $authModuleOptions;
    protected $mapper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->appModuleOptions = $appModuleOptions = new \UnicaenApp\Options\ModuleOptions([
            'ldap' => [
                'connection' => [
                    'default' => [
                        'params' => [
                            'host'                => 'host.domain.fr',
                            'username'            => "uid=xxxxxxxxx,ou=xxxxxxxxxx,dc=domain,dc=fr",
                            'password'            => "xxxxxxxxxxxx",
                            'baseDn'              => "ou=xxxxxxxxxxx,dc=domain,dc=fr",
                            'bindRequiresDn'      => true,
                            'accountFilterFormat' => "(&(objectClass=posixAccount)(supannAliasLogin=%s))",
                        ]
                    ]
                ]
            ],
        ]);
        $this->authModuleOptions = $authModuleOptions = new \UnicaenAuth\Options\ModuleOptions([
            'usurpation_allowed_usernames' => ['usurpateur'],
        ]);

        $this->mapper = $mapper = $this->getMock('ZfcUser\Mapper\User', ['findByUsername', 'findByEmail']);

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager', ['get']);
        $serviceManager->expects($this->any())
                       ->method('get')
                       ->will($this->returnCallback(function($serviceName) use ($authModuleOptions, $appModuleOptions, $mapper) {
                           if ('zfcuser_module_options' === $serviceName) {
                               return new \ZfcUser\Options\ModuleOptions();
                           }
                           if ('unicaen-app_module_options' === $serviceName) {
                               return $appModuleOptions;
                           }
                           if ('unicaen-auth_module_options' === $serviceName) {
                               return $authModuleOptions;
                           }
                           if ('zfcuser_user_mapper' === $serviceName) {
                               return $mapper;
                           }
                           return null;
                       }));

        $this->adapter = new Ldap();
        $this->adapter->setServiceManager($serviceManager)
                      ->setEventManager(new EventManager());
    }

    public function testCanProvideDefaultLdapAuthAdapter()
    {
        $adapter = $this->adapter->getLdapAuthAdapter();
        $this->assertInstanceOf('Zend\Authentication\Adapter\Ldap', $adapter);

        $appModuleLdapOptions = $this->appModuleOptions->getLdap();
        $connectionNames = array_keys($appModuleLdapOptions['connection']);
        $connectionParams = array_map(function($connection) { return $connection['params']; }, $appModuleLdapOptions['connection']);
        $this->assertEquals(array_combine($connectionNames, $connectionParams), $adapter->getOptions());
    }

    public function testAuthenticatingReturnsNullIfAlreadyStatisfied()
    {
        $event = new AdapterChainEvent();
        $this->adapter->setSatisfied();
        $this->assertNull($this->adapter->authenticate($event));
        $this->assertEquals($event->getCode(), Result::SUCCESS);
    }

    public function testUsurpationWithAllowedUsernameAndSuccessfulAuthentication()
    {
        $this->authModuleOptions->setUsurpationAllowedUsernames(['usurpateur']);
        $event = new AdapterChainEvent();
        $result = $this->_authenticateWithUsurpation(Result::SUCCESS, $event);

        $this->assertTrue($result);
        $this->assertTrue($this->adapter->isSatisfied());
        $this->assertEquals(['is_satisfied' => true, 'identity' => 'usurpe'], $this->adapter->getStorage()->read());

        $this->assertEquals("userAuthenticated", $event->getName());
        $this->assertEquals(Result::SUCCESS, $event->getCode());
        $this->assertEquals('usurpe', $event->getIdentity());
        $this->assertTrue($event->propagationIsStopped());
    }

    public function testUsurpationWithAllowedUsernameAndUnsuccessfulAuthentication()
    {
        $this->authModuleOptions->setUsurpationAllowedUsernames(['usurpateur']);
        $event = new AdapterChainEvent();
        $result = $this->_authenticateWithUsurpation(Result::FAILURE, $event);

        $this->assertFalse($result);
        $this->assertFalse($this->adapter->isSatisfied());
        $this->assertEquals(['is_satisfied' => false], $this->adapter->getStorage()->read());

        $this->assertNull($event->getName());
        $this->assertEquals(Result::FAILURE, $event->getCode());
        $this->assertNull($event->getIdentity());
        $this->assertFalse($event->propagationIsStopped());
    }

    public function testUsurpationWithNotAllowedUsernameAndSuccessfulAuthentication()
    {
        $this->authModuleOptions->setUsurpationAllowedUsernames([]);
        $event = new AdapterChainEvent();
        $result = $this->_authenticateWithUsurpation(Result::SUCCESS, $event);

        $this->assertTrue($result);
        $this->assertTrue($this->adapter->isSatisfied());
        $this->assertEquals(['is_satisfied' => true, 'identity' => 'usurpateur'], $this->adapter->getStorage()->read());

        $this->assertEquals("userAuthenticated", $event->getName());
        $this->assertTrue($event->propagationIsStopped());
        $this->assertEquals('usurpateur', $event->getIdentity());
    }

    public function testUsurpationWithNotAllowedUsernameAndUnsuccessfulAuthentication()
    {
        $this->authModuleOptions->setUsurpationAllowedUsernames([]);
        $event = new AdapterChainEvent();
        $result = $this->_authenticateWithUsurpation(Result::FAILURE, $event);

        $this->assertFalse($result);
        $this->assertFalse($this->adapter->isSatisfied());
        $this->assertEquals(['is_satisfied' => false], $this->adapter->getStorage()->read());

        $this->assertNull($event->getName());
        $this->assertEquals(Result::FAILURE, $event->getCode());
        $this->assertNull($event->getIdentity());
        $this->assertFalse($event->propagationIsStopped());
    }

    protected function _authenticateWithUsurpation($authenticationResultCode, AdapterChainEvent &$event)
    {
        $usernameUsurpateur = 'usurpateur';
        $usernameUsurpe     = 'usurpe';
        $username           = $usernameUsurpateur . Ldap::USURPATION_USERNAMES_SEP . $usernameUsurpe;

        $ldapAuthAdapter = $this->getMock('Zend\Authentication\Adapter\Ldap', ['setUsername', 'setPassword', 'authenticate']);
        $ldapAuthAdapter->expects($this->once())
                        ->method('setUsername')
                        ->with($usernameUsurpateur)
                        ->will($this->returnSelf());
        $ldapAuthAdapter->expects($this->once())
                        ->method('setPassword')
                        ->will($this->returnSelf());
        $ldapAuthAdapter->expects($this->once())
                        ->method('authenticate')
                        ->will($this->returnValue(new Result($authenticationResultCode, $usernameUsurpateur)));
        $this->adapter->setLdapAuthAdapter($ldapAuthAdapter);

        $request = new Request();
        $request->setPost(new Parameters(['identity' => $username, 'credential' => "xxxxx"]));
        $event->setRequest($request);

        return $this->adapter->authenticate($event);
    }
}