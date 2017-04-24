<?php
namespace UnicaenAppTest\Form\View\Helper;

use UnicaenApp\Form\View\Helper\FormControlGroup;
use UnicaenApp\Form\Element\DateInfSup;
use UnicaenApp\Form\Element\SearchAndSelect;
use Zend\Form\Element\Text;

/**
 * Description of FormControlGroupTest
 *
 * @property FormControlGroup $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class FormControlGroupTest extends AbstractTest
{
    protected $helperClass = 'UnicaenApp\Form\View\Helper\FormControlGroup';
    protected $delegateHelperClass;
    protected $renderer;

    public function setUp()
    {
        parent::setUp();
        
        $helpers = array(
            'formDateInfSup'      => $formDateInfSup      = $this->getMock('UnicaenApp\Form\View\Helper\FormDateInfSup', array('__invoke')),
            'formSearchAndSelect' => $formSearchAndSelect = $this->getMock('UnicaenApp\Form\View\Helper\FormSearchAndSelect', array('__invoke')),
            'formElement'         => $formElement         = $this->getMock('Zend\Form\View\Helper\FormElement', array('__invoke')),
            'formLabel'           => $formLabel           = $this->getMock('Zend\Form\View\Helper\FormLabel', array('__invoke')),
            'formElementErrors'   => $formElementErrors   = $this->getMock('Zend\Form\View\Helper\FormElementErrors', array('__invoke')),
        );
        foreach ($helpers as $name => $helper) {
            $helper
                    ->expects($this->any())
                    ->method('__invoke')
                    ->will($this->returnValue("$name markup"));
        }
        
        $renderer = $this->getMock('Zend\View\Renderer\PhpRenderer', array('plugin'));
        $map = array(
            array('formDateInfSup', null, $formDateInfSup),
            array('formSearchAndSelect',  null, $formSearchAndSelect),
            array('formElement',  null, $formElement),
            array('formLabel',  null, $formLabel),
            array('formElementErrors',  null, $formElementErrors),
        );
        $renderer
                ->expects($this->any())
                ->method('plugin')
                ->will($this->returnValueMap($map));
        
        $this->helper->setView($renderer);
    }
    
    public function testRenderingEntryPoints()
    {
        $element = new Text('text');
        $markup = $this->helper->render($element);
        $this->assertEquals($markup, $this->helper->__invoke($element));
    }
    
    public function testCanDelegateFullRenderingToDateInfSupHelper()
    {
        $element = new DateInfSup('dates');
        $this->assertEquals($this->getExpected('form-control-group/dateinfsup.phtml'), $this->helper->render($element));
    }
    
    public function testCanDelegatePartialRenderingToSearchAndSelectHelper()
    {
        $element = new SearchAndSelect('person');
        $this->assertEquals($this->getExpected('form-control-group/searchandselect.phtml'), $this->helper->render($element));
    }
    
    public function testCanRenderDefault()
    {
        $element = new Text('text');
        $this->assertTrue($this->helper->getIncludeLabel());
        $this->assertFalse($this->helper->getAddClearButton());
        $this->assertEquals($this->getExpected('form-control-group/default.phtml'), $this->helper->render($element));
    }
    
    public function testCanRenderWithoutLabel()
    {
        $this->helper->setIncludeLabel(false);
        $element = new Text('text');
        $this->assertFalse($this->helper->getIncludeLabel());
        $this->assertEquals($this->getExpected('form-control-group/without-label.phtml'), $this->helper->render($element));
    }
    
    public function testCanRenderWithClearButton()
    {
        $this->helper->setAddClearButton(true);
        $element = new Text('text');
        $this->assertTrue($this->helper->getAddClearButton());
        $this->assertEquals($this->getExpected('form-control-group/with-clear-button.phtml'), $this->helper->render($element));
    }
    
    public function testCanRenderErrors()
    {
        $element = new Text('text');
        $element->setMessages(array("Erroooor!"));
        $this->assertEquals($this->getExpected('form-control-group/errors.phtml'), $this->helper->render($element));
    }
}