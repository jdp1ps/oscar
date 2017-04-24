<?php
namespace UnicaenAppTest\Form\Element;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Form\Element\MultipageFormNav;

/**
 * Description of MultipageFormNavTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MultipageFormNavTest extends PHPUnit_Framework_TestCase
{
    protected $element;
    
    protected function setUp()
    {
        $this->element = new MultipageFormNav('elem');
    }
    
    public function testConstructorByDefautSetsDefaultName()
    {
        $element = new MultipageFormNav();
        $this->assertEquals(MultipageFormNav::NAME, $element->getName());
    }
    
    public function testConstructorInitializesPropertiesWithDefaultValues()
    {
        $this->assertFalse($this->element->getActivatePrevious());
        $this->assertTrue($this->element->getActivateNext());
        $this->assertFalse($this->element->getActivateSubmit());
        $this->assertTrue($this->element->getActivateCancel());
        $this->assertFalse($this->element->getActivateConfirm());
    }
}