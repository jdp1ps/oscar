<?php
namespace UnicaenAuthTest\Authentication\Adapter;

use CAS_GracefullTerminationException;
use PHPUnit_Framework_TestCase;
use UnicaenApp\Exception;
use UnicaenAuth\Authentication\Adapter\Cas;
use Zend\EventManager\EventManager;
use ZfcUser\Authentication\Adapter\AdapterChainEvent;
use Zend\Authentication\Result;

define ('__VENDOR_DIR__', __DIR__ . '/../../../../vendor');

require_once __VENDOR_DIR__ . '/intouch/phpcas/CAS.php';

/**
 * Description of CasTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class CasTest extends PHPUnit_Framework_TestCase
{
    protected $adapter;
    protected $moduleOptions;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->moduleOptions = $moduleOptions = new \UnicaenAuth\Options\ModuleOptions([
            'cas' => [
                'connection' => [
                    'default' => [
                        'params' => [
                            'hostname' => 'cas.unicaen.fr',
                            'port' => 443,
                            'version' => "2.0",
                            'uri' => "",
                            'debug' => false,
                        ],
                    ],
                ],
            ],
        ]);

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager', ['get']);
        $serviceManager->expects($this->any())
                       ->method('get')
                       ->will($this->returnCallback(function($serviceName) use ($moduleOptions) {
                           if ('zfcuser_module_options' === $serviceName) {
                               return new \ZfcUser\Options\ModuleOptions();
                           }
                           if ('unicaen-auth_module_options' === $serviceName) {
                               return $moduleOptions;
                           }
                           if ('router' === $serviceName) {
                               $router = new \Zend\Mvc\Router\Http\TreeRouteStack();
                               $router->setBaseUrl('/appli')->setRequestUri(new \Zend\Uri\Http('/request'));
                               return $router;
                           }
                           return null;
                       }));

        $this->adapter = new Cas();
        $this->adapter->setServiceManager($serviceManager)
                      ->setEventManager(new EventManager());
    }

    public function getInvalidCasOptions()
    {
        return [
            [['other' => []]],
            [['connection' => []]],
            [['connection' => ['default'=> []]]],
            [['connection' => ['default'=> ['params' => []]]]],
        ];
    }

    /**
     * @dataProvider getInvalidCasOptions
     * @expectedException Exception
     */
    public function testThrowsExceptionIfNoCasParamSpecified($config)
    {
        $this->moduleOptions->setCas($config);
        $this->adapter->authenticate(new AdapterChainEvent());
    }

    public function testAuthenticateReturnsNullIfNoCasConfigSpecified()
    {
        $this->moduleOptions->setCas([]);
        $result = $this->adapter->authenticate(new AdapterChainEvent());
        $this->assertNull($result);
    }

    public function testCanActivateCasDebugMode()
    {
        $this->moduleOptions->setCas([
            'connection' => [
                'default' => [
                    'params' => [
                        'hostname' => 'cas.unicaen.fr',
                        'port' => 443,
                        'version' => "2.0",
                        'uri' => "",
                        'debug' => true, // debug mode
                    ],
                ],
            ],
        ]);

        $casClient = $this->getMock('phpCAS', ['setDebug', 'client', 'setNoCasServerValidation']);
        $casClient->staticExpects($this->once())
                  ->method('setDebug');
        $this->adapter->setCasClient($casClient);

        $this->adapter->getCasClient();
    }

    public function testCanRedirectToCasIfNotAuthenticated()
    {
        CAS_GracefullTerminationException::throwInsteadOfExiting();

        ob_start();
        try {
            $result = $this->adapter->authenticate(new AdapterChainEvent());
            $this->fail("Exception CAS_GracefullTerminationException non levée.");
        }
        catch (CAS_GracefullTerminationException $e) {

        }
        $result = ob_get_clean();

        $expected = <<<EOS
<html><head><title>CAS Authentication wanted!</title></head><body><h1>CAS Authentication wanted!</h1><p>You should already have been redirected to the CAS server. Click <a href="https://cas.unicaen.fr/login?service=http%3A%2F%2F%3A">here</a> to continue.</p><hr><address>phpCAS 1.3.2+ using server <a href="https://cas.unicaen.fr/">https://cas.unicaen.fr/</a> (CAS 2.0)</a></address></body></html>
EOS;
        $this->assertEquals($expected, $result);
    }

    public function testAuthenticateReturnsTrueWhenAuthenticationSucceeds()
    {
        $casClient = $this->getMock('phpCAS', ['client', 'forceAuthentication', 'getUser']);
        $casClient->staticExpects($this->once())
                  ->method('getUser')
                  ->will($this->returnValue($username = 'username'));

        $this->adapter->setCasClient($casClient);

        $event = new AdapterChainEvent();

        $result = $this->adapter->authenticate($event);

        $this->assertTrue($result);
        $this->assertTrue($this->adapter->isSatisfied());
        $this->assertEquals(['is_satisfied' => true, 'identity' => $username], $this->adapter->getStorage()->read());

        $this->assertEquals("userAuthenticated", $event->getName());
        $this->assertEquals(Result::SUCCESS, $event->getCode());
        $this->assertEquals($username, $event->getIdentity());
        $this->assertTrue($event->propagationIsStopped());
    }

    public function testLogoutReturnsNullIfNoCasConfigSpecified()
    {
        $this->moduleOptions->setCas([]);
        $result = $this->adapter->logout(new AdapterChainEvent());
        $this->assertNull($result);
    }

    public function testCanLogoutFromCasWithRedirectService()
    {
        $casClient = $this->getMock('phpCAS', ['client', 'isAuthenticated', 'logoutWithRedirectService']);
        $casClient->staticExpects($this->once())
                  ->method('isAuthenticated')
                  ->will($this->returnValue(true));
        $casClient->staticExpects($this->once())
                  ->method('logoutWithRedirectService');

        $this->adapter->setCasClient($casClient);

        $this->adapter->logout(new AdapterChainEvent());
    }
}