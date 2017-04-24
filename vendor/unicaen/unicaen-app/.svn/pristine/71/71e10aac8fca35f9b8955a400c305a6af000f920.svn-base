<?php
namespace UnicaenAppTest\Service\Doctrine;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Service\Doctrine\MultipleDbAbstractFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * Description of MultipleDbAbstractFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MultipleDbAbstractFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MultipleDbAbstractFactory 
     */
    protected $factory;
    
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $config = array(
        'doctrine' => array(
            'connection'            => array('orm_default' => array()),
            'configuration'         => array('orm_default' => array()),
            'driver'                => array('orm_default' => array('class' => 'Doctrine\ORM\Mapping\Driver\DriverChain',)),
            'entitymanager'         => array('orm_default' => array()),
            'eventmanager'          => array('orm_default' => array()),
            'sql_logger_collector'  => array('orm_default' => array()),
            'entity_resolver'       => array('orm_default' => array()),
            'authenticationadapter' => array('orm_default' => array()),
            'authenticationstorage' => array('orm_default' => array()),
            'authenticationservice' => array('orm_default' => array()),
        )
    );
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->factory = new MultipleDbAbstractFactory();
        $this->serviceManager = new ServiceManager(); //$this->getMock('Zend\ServiceManager\ServiceManager'/*, array('get')*/);
    }
    
    public function getInvalidRequestedName()
    {
        return array(
            'null'          => array(null),
            'chaine-vide'   => array(''),
            'point'         => array('.'),
            'prefixe-seul'  => array('doctrine'),
            'type-seul'     => array('configuration'),
            'prefixe-point' => array('doctrine.'),
            'sans-point'    => array('doctrine configuration'),
            'point-type'    => array('.configuration'),
            'pas-de-nom1'   => array('doctrine.configuration'),
            'pas-de-nom2'   => array('doctrine.configuration.'),
            'trop-long'     => array('doctrine.configuration.orm_default.xxxxx'),
        );
    }
    
    /**
     * @dataProvider getInvalidRequestedName
     * @param string $requestedName
     */
    public function testCanCreateServiceWithNameReturnsFalseForInvalidRequestedName($requestedName)
    {
        $result = $this->factory->canCreateServiceWithName($this->serviceManager, null, $requestedName);
        $this->assertFalse($result);
    }
    
    public function getUnknownServiceType()
    {
        return array(
            array('doctrine.unknown-service-name.nevermind'),
            array('doctrine.connectionxxxxxx.nevermind'),
        );
    }
    
    /**
     * @dataProvider getUnknownServiceType
     * @param string $requestedName
     */
    public function testCanCreateServiceWithNameReturnsFalseForUnknownServiceType($requestedName)
    {
        $result = $this->factory->canCreateServiceWithName($this->serviceManager, null, $requestedName);
        $this->assertFalse($result);
    }
    
    public function getValidRequestedName()
    {
        return array(
            // service name                                     // expected factory class
            array('doctrine.connection.orm_default',            'DoctrineORMModule\Service\DBALConnectionFactory'),
            array('doctrine.configuration.orm_default',         'DoctrineORMModule\Service\ConfigurationFactory'),
            array('doctrine.entitymanager.orm_default',         'DoctrineORMModule\Service\EntityManagerFactory'),
            array('doctrine.driver.orm_default',                'DoctrineModule\Service\DriverFactory'),
            array('doctrine.eventmanager.orm_default',          'DoctrineModule\Service\EventManagerFactory'),
            array('doctrine.entity_resolver.orm_default',       'DoctrineORMModule\Service\EntityResolverFactory'),
            array('doctrine.sql_logger_collector.orm_default',  'DoctrineORMModule\Service\SQLLoggerCollectorFactory'),
            array('doctrine.authenticationadapter.orm_default', 'DoctrineModule\Service\Authentication\AdapterFactory'),
            array('doctrine.authenticationstorage.orm_default', 'DoctrineModule\Service\Authentication\StorageFactory'),
            array('doctrine.authenticationservice.orm_default', 'DoctrineModule\Service\Authentication\AuthenticationServiceFactory'),
        );
    }
    
    /**
     * @dataProvider getValidRequestedName
     * @param string $requestedName
     */
    public function testCanCreateServiceWithNameReturnsTrueForValidRequestedName($requestedName)
    {
        $result = $this->factory->canCreateServiceWithName($this->serviceManager, null, $requestedName);
        $this->assertTrue($result);
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\LogicException
     */
    public function testCreateServiceWithNameThrowsExceptionIfUnknownServiceTypeSpecified()
    {
        $this->factory->createServiceWithName($this->serviceManager, null, 'doctrine.unknown_service.orm_default');
    }
    
    /**
     * @dataProvider getValidRequestedName
     * @param string $requestedName
     * @param string $expectedFactoryClass
     */
    public function testCreateServiceWithNameReturnsFactory($requestedName, $expectedFactoryClass)
    {
        $factory = $this->factory->createServiceWithName($this->serviceManager, null, $requestedName);
        $this->assertEquals($expectedFactoryClass, $factory);
    }
    
    /**
     * @dataProvider getValidRequestedName
     * @param string $requestedName
     * @param string $expectedFactoryClass
     */
    public function testCreateServiceWithNameReturnsInjectedFactory($requestedName, $expectedFactoryClass)
    {
        list(, $type, ) = explode('.', $requestedName);
        $doctrineFactory = $this->getMock($expectedFactoryClass, array('createService'), array(), '', false);
        $doctrineFactory->expects($this->once())
                        ->method('createService')
                        ->with($this->serviceManager)
                        ->will($this->returnValue('service-instance'));
        $this->factory->setServiceFactory($doctrineFactory, $type);
                             
        $factory = $this->factory->createServiceWithName($this->serviceManager, null, $requestedName);
        $this->assertEquals('service-instance', $factory);
    }
}

/**
 * Ce qui suit permet de redéfinir des classes Doctrine utilisées par la classe testée
 * afin de faciliter les tests.
 * 
 * NB: le namespace doit être le même que celui de la classe orirignale.
 */
namespace DoctrineModule\Service;

class DriverFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function __construct($serviceName) { }
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sl) { return __CLASS__; }
}

class EventManagerFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function __construct($serviceName) { }
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sl) { return __CLASS__; }
}

namespace DoctrineORMModule\Service;

class ConfigurationFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function __construct($serviceName) { }
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sl) { return __CLASS__; }
}

class DBALConnectionFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function __construct($serviceName) { }
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sl) { return __CLASS__; }
}

class EntityManagerFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function __construct($serviceName) { }
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sl) { return __CLASS__; }
}

class EntityResolverFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function __construct($serviceName) { }
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sl) { return __CLASS__; }
}

class SQLLoggerCollectorFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function __construct($serviceName) { }
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sl) { return __CLASS__; }
}

namespace DoctrineModule\Service\Authentication; 

class AdapterFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function __construct($serviceName) { }
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sl) { return __CLASS__; }
}

class StorageFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function __construct($serviceName) { }
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sl) { return __CLASS__; }
}

class AuthenticationServiceFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function __construct($serviceName) { }
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sl) { return __CLASS__; }
}