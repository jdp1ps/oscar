<?php

namespace UnicaenAuthTest\Provider\Role;

use PHPUnit_Framework_TestCase;

/**
 * Description of ConfigServiceFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
abstract class BaseServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $serviceLocator;
    protected $factoryClass;
    protected $factory;
    protected $serviceClass;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->factory        = new $this->factoryClass();
        $this->serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface', []);
    }
}