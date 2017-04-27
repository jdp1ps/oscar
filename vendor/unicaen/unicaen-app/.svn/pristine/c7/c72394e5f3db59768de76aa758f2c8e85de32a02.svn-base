<?php
namespace UnicaenAppTest\Mapper\Ldap;

use UnicaenApp\Mapper\Ldap\StructureFactory;

/**
 * 
 *
 * @property StructureFactory $factory
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class StructureFactoryTest extends CommonFactoryTest
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->factory = new StructureFactory();
    }
    
    public function testCanCreateService()
    {
        $mapper = $this->factory->createService($this->serviceManager);
        $this->assertInstanceOf('UnicaenApp\Mapper\Ldap\Structure', $mapper);
        $this->assertEquals(
                $params = $this->options['ldap']['connection']['default']['params'], 
                array_intersect_key($mapper->getLdap()->getOptions(), $params));
    }
}