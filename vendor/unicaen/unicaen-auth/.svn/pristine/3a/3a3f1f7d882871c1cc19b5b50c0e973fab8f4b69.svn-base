<?php
namespace UnicaenAuthTest\Authentication\Adapter;

use PHPUnit_Framework_TestCase;
use UnicaenAuth\Authentication\Adapter\AbstractFactory;
use UnicaenAuth\Service\User;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Description of AbstractFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AbstractFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $factory;

    protected function setUp()
    {
        $this->factory = new AbstractFactory();
    }

    public function getInvalidServiceClassName()
    {
        return [
            'unknown-class'   => ['UnicaenAuth\Authentication\Adapter\Xxxx'],
            'wrong-namespace' => ['Any\Other\Namespaced\Class'],
        ];
    }

    /**
     * @dataProvider getInvalidServiceClassName
     * @param string $serviceClassName
     */
    public function testCanRefuseCreatingServiceWithInvalidName($serviceClassName)
    {
        $this->assertFalse($this->factory->canCreateServiceWithName(new ServiceManager(), null, $serviceClassName));
    }

    public function getValidServiceClassName()
    {
        return [
            'cas'  => ['UnicaenAuth\Authentication\Adapter\Cas'],
            'db'   => ['UnicaenAuth\Authentication\Adapter\Db'],
            'ldap' => ['UnicaenAuth\Authentication\Adapter\Ldap'],
        ];
    }

    /**
     * @dataProvider getValidServiceClassName
     * @param string $serviceClassName
     */
    public function testCanAcceptCreatingServiceWithValidName($serviceClassName)
    {
        $this->assertTrue($this->factory->canCreateServiceWithName(new ServiceManager(), null, $serviceClassName));
    }

    /**
     * @dataProvider getInvalidServiceClassName
     * @expectedException \UnicaenApp\Exception
     * @param string $serviceClassName
     */
    public function testCreateServiceWithNameThrowsExceptionIfInvalidServiceSpecified($serviceClassName)
    {
        $this->factory->createServiceWithName(new ServiceManager(), null, $serviceClassName);
    }

    /**
     * @dataProvider getValidServiceClassName
     * @param string $serviceClassName
     */
    public function testCanCreateServiceWithName($serviceClassName)
    {
        $eventManager = new EventManager();

        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceManager', ['get']);
        $serviceLocator->expects($this->any())
                       ->method('get')
                       ->will($this->returnCallback(function($serviceName) use ($eventManager) {
                           if ('unicaen-auth_user_service' === $serviceName) {
                               return new User();
                           }
                           if ('event_manager' === $serviceName) {
                               return $eventManager;
                           }
                           return null;
                       }));

        $adapter = $this->factory->createServiceWithName($serviceLocator, null, $serviceClassName);

        $this->assertInstanceOf($serviceClassName, $adapter);

        if ($adapter instanceof EventManagerAwareInterface) {
            $this->assertSame($eventManager, $adapter->getEventManager());
        }
    }
}