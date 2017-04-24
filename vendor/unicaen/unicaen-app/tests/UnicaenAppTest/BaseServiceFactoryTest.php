<?php

namespace UnicaenAppTest;

use PHPUnit_Framework_TestCase;

/**
 * Description of BaseLdapServiceFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
abstract class BaseServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\FactoryInterface
     */
    protected $factory;
    
    /**
     * @var string
     */
    protected $factoryClass;
    
    /**
     * @var mixed
     */
    protected $serviceClass;
    
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceManager;
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->factory        = new $this->factoryClass();
        $this->serviceManager = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', array('get'));
    }
}