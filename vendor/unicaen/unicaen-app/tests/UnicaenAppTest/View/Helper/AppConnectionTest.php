<?php
namespace UnicaenAppTest\View\Helper;

use PHPUnit_Framework_TestCase;
use UnicaenApp\View\Helper\AppConnection;

/**
 * Description of AppConnectionTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AppConnectionTest extends PHPUnit_Framework_TestCase
{
    protected $helper;
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->helper = new AppConnection();
    }
    
    public function testReturnsSelfWhenInvoked()
    {
        $this->assertSame($this->helper, $this->helper->__invoke());
    }
    
    public function testToStringMethodReturnsEmptyString()
    {
        $this->assertEquals('', "" . $this->helper);
    }
}