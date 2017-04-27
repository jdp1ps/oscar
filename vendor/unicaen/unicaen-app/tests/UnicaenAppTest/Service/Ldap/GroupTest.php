<?php
namespace UnicaenAppTest\Service\Ldap;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Service\Ldap\Group as GroupService;

/**
 * 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class GroupTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var GroupService
     */
    protected $service;
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->service = new GroupService();
    }
    
    public function testConstructorPropagatesLdapArgToMapper()
    {
        $service = new GroupService($ldap = new \Zend\Ldap\Ldap());
        $this->assertSame($ldap, $service->getMapper()->getLdap());
    }
    
    public function testGetMapperReturnsGroupMapper()
    {
        $this->assertInstanceOf('UnicaenApp\Mapper\Ldap\Group', $this->service->getMapper());
    }
}