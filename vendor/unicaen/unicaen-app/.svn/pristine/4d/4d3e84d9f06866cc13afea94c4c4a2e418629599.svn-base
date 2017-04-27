<?php
namespace UnicaenAppTest\Service\Ldap;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Service\Ldap\People as PeopleService;

/**
 * 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class PeopleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PeopleService
     */
    protected $service;
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->service = new PeopleService();
    }
    
    public function testConstructorPropagatesLdapArgToMapper()
    {
        $service = new PeopleService($ldap = new \Zend\Ldap\Ldap());
        $this->assertSame($ldap, $service->getMapper()->getLdap());
    }
    
    public function testGetMapperReturnsPeopleMapper()
    {
        $this->assertInstanceOf('UnicaenApp\Mapper\Ldap\People', $this->service->getMapper());
    }
}