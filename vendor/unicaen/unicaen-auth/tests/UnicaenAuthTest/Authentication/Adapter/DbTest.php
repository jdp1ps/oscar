<?php
namespace UnicaenAuthTest\Authentication\Adapter;

use PDOException;
use PHPUnit_Framework_TestCase;
use UnicaenAuth\Authentication\Adapter\Db;
use UnicaenAuth\Options\ModuleOptions;
use Zend\Http\PhpEnvironment\Request;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\Stdlib\Parameters;
use ZfcUser\Authentication\Adapter\AdapterChainEvent;

/**
 * Description of DbTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class DbTest extends PHPUnit_Framework_TestCase
{
    protected $adapter;
    protected $moduleOptions;
    protected $mapper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->moduleOptions = $moduleOptions = new ModuleOptions([
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

        $this->mapper = $mapper = $this->getMock('ZfcUser\Mapper\User', ['findByUsername', 'findByEmail']);

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager', ['get']);
        $serviceManager->expects($this->any())
                       ->method('get')
                       ->will($this->returnCallback(function($serviceName) use ($moduleOptions, $mapper) {
                           if ('zfcuser_module_options' === $serviceName) {
                               return new \ZfcUser\Options\ModuleOptions();
                           }
                           if ('unicaen-auth_module_options' === $serviceName) {
                               return $moduleOptions;
                           }
                           if ('zfcuser_user_mapper' === $serviceName) {
                               return $mapper;
                           }
                           return null;
                       }));

        $this->adapter = new Db();
        $this->adapter->setServiceManager($serviceManager);
    }

    public function getException()
    {
        return [
            [new PDOException()],
            [new ServiceNotFoundException()],
        ];
    }

    /**
     * @dataProvider getException
     */
    public function testAuthenticateReturnsFalseIfExceptionThrown($exception)
    {
        $this->mapper->expects($this->once())
                     ->method($this->logicalOr('findByUsername', 'findByEmail'))
                     ->will($this->throwException($exception));

        $request = new Request();
        $request->setPost(new Parameters(['identity' => 'bob', 'credential' => "xxxxx"]));

        $event = new AdapterChainEvent();
        $event->setRequest($request);

        $result = $this->adapter->authenticate($event);
        $this->assertFalse($result);
    }

    public function testAuthenticateReturnsParentMethodResult()
    {
        $request = new Request();
        $request->setPost(new Parameters(['identity' => 'bob', 'credential' => "xxxxx"]));

        $event = new AdapterChainEvent();
        $event->setRequest($request);

        $result = $this->adapter->authenticate($event);
        $this->assertFalse($result);
    }
}