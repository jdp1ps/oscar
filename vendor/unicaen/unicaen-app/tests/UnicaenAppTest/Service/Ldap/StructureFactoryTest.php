<?php
namespace UnicaenAppTest\Service\Ldap;

use UnicaenApp\Service\Ldap\StructureFactory;

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
        $service = $this->factory->createService($this->serviceManager);
        $this->assertInstanceOf('UnicaenApp\Service\Ldap\Structure', $service);
        $this->assertEquals(
                $params = $this->options['ldap']['connection']['default']['params'], 
                array_intersect_key($service->getLdap()->getOptions(), $params));
    }
}