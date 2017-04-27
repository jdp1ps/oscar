<?php
namespace UnicaenAppTest\Mapper\Ldap;

use UnicaenApp\Mapper\Ldap\GroupFactory;

/**
 * 
 *
 * @property GroupFactory $factory
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class GroupFactoryTest extends CommonFactoryTest
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->factory = new GroupFactory();
    }
    
    public function testCanCreateService()
    {
        $mapper = $this->factory->createService($this->serviceManager);
        $this->assertInstanceOf('UnicaenApp\Mapper\Ldap\Group', $mapper);
        $this->assertEquals(
                $params = $this->options['ldap']['connection']['default']['params'], 
                array_intersect_key($mapper->getLdap()->getOptions(), $params));
    }
}