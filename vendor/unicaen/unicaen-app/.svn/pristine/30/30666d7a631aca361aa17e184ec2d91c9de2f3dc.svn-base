<?php
namespace UnicaenAppTest\Service\Ldap;

use PHPUnit_Framework_TestCase;

/**
 * Description of AbstractServiceTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AbstractServiceTest extends PHPUnit_Framework_TestCase
{
    protected $service;
    protected $mapper;
    protected $ldap;
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->mapper  = $this->getMockForAbstractClass('UnicaenApp\Mapper\Ldap\AbstractMapper');
        $this->ldap    = $this->getMock('Zend\Ldap\Ldap');
        $this->service = $this->getMockForAbstractClass('UnicaenApp\Service\Ldap\AbstractService');
        
        $this->service->expects($this->any())
                      ->method('getMapper')
                      ->will($this->returnValue($this->mapper));
        
        $this->service->setMapper($this->mapper)
                      ->setLdap($this->ldap);
    }
    
    public function testSettingLdapObjectPropagatesItToMapper()
    {
        $this->service->setLdap($this->ldap);
        $this->assertSame($this->ldap, $this->service->getLdap());
        $this->assertSame($this->ldap, $this->service->getMapper()->getLdap());
    }
}