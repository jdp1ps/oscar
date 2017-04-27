<?php
namespace UnicaenAppTest\Service\Ldap;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Service\Ldap\Structure as StructureService;

/**
 * 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class StructureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var StructureService
     */
    protected $service;
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->service = new StructureService();
    }
    
    public function testConstructorPropagatesLdapArgToMapper()
    {
        $service = new StructureService($ldap = new \Zend\Ldap\Ldap());
        $this->assertSame($ldap, $service->getMapper()->getLdap());
    }
    
    public function testGetMapperReturnsStructureMapper()
    {
        $this->assertInstanceOf('UnicaenApp\Mapper\Ldap\Structure', $this->service->getMapper());
    }
}