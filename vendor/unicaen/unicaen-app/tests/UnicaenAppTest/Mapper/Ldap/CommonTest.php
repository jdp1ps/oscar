<?php
namespace UnicaenAppTest\Mapper\Ldap;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Mapper\Ldap\AbstractMapper;
use Zend\Ldap\Ldap;

/**
 * Description of CommonTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class CommonTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $entityClassName;
    
    /**
     * @var AbstractMapper 
     */
    protected $mapper;
    
    /**
     * @var Ldap Mock object
     */
    protected $ldap;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->ldap = $this->getMock('Zend\Ldap\Ldap', array('searchEntries'));
    }
}