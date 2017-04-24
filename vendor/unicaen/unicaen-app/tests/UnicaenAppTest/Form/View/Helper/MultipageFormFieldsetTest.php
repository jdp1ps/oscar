<?php
namespace UnicaenAppTest\Form\View\Helper;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Form\View\Helper\MultipageFormFieldset;
use Zend\Form\View\HelperConfig;
use Zend\View\Renderer\PhpRenderer;

/**
 * Description of MultipageFormFieldsetTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MultipageFormFieldsetTest extends PHPUnit_Framework_TestCase
{
    protected $helper;
    protected $renderer;

    public function setUp()
    {
        $this->helper = new MultipageFormFieldset();

        $this->renderer = new PhpRenderer;
        $helpers = $this->renderer->getHelperPluginManager();
        $config = new HelperConfig();
        $config->configureServiceManager($helpers);

        $mockMultipageFormRow = $this->getMock('\UnicaenApp\Form\View\Helper\MultipageFormRow');
        $mockMultipageFormRow->expects($this->any())
                             ->method('__invoke')
                             ->withAnyParameters()
                             ->will($this->returnValue('<b>MultipageFormRow content</b>'));
        $helpers->setService('multipageFormRow', $mockMultipageFormRow);
        
        $this->helper->setView($this->renderer);
        
        $fieldset = new \UnicaenAppTest\Form\TestAsset\IdentiteFieldset('fieldset');
        $fieldset->setLabel("Step fieldset");
        $this->helper->getView()->fieldset = $fieldset;
        $this->helper->getView()->stepIndex = 1;
        $this->helper->getView()->stepCount = 3;
    }
    
    /**
     * @expectedException \Zend\View\Exception\InvalidArgumentException
     */
    public function testInvokingBeforeSettingFieldsetThrowsException()
    {
        unset($this->helper->getView()->fieldset);
        $this->helper->__invoke();
    }
    
    /**
     * @expectedException \Zend\View\Exception\InvalidArgumentException
     */
    public function testInvokingWithInvalidFieldsetTypeThrowsException()
    {
        $this->helper->getView()->fieldset = new \Zend\Form\Element('elem');
        $this->helper->__invoke();
    }
    
    /**
     * @expectedException \Zend\View\Exception\InvalidArgumentException
     */
    public function testInvokingWithEmptyFieldsetThrowsException()
    {
        $this->helper->getView()->fieldset->remove('nom')->remove('prenom')->remove('civ');
        $this->helper->__invoke();
    }
    
    public function testCanTranslate()
    {
        $mockTranslator = $this->getMock('Zend\I18n\Translator\Translator');
        $mockTranslator->expects($this->any())
                       ->method('translate')
                       ->will($this->returnValue('translated content'));
        
        $this->helper->setTranslator($mockTranslator);
        $this->assertTrue($this->helper->hasTranslator());
        
        $this->helper->__invoke();
    }
    
    public function testInvokingReturnsString()
    {
        $markup = $this->helper->__invoke();
        $this->assertInternalType('string', $markup);
    }
    
    public function testRenderingDelegatesToMultipageFormRowHelper()
    {
        $mockMultipageFormRow = $this->renderer->getHelperPluginManager()->get('multipageFormRow');
        $mockMultipageFormRow->expects($this->exactly(count($this->helper->getView()->fieldset->getElements())))
                             ->method('__invoke');
        $this->helper->__invoke();
    }
    
    public function testRenderingWithoutStepAttributesGeneratesForm()
    {
        unset($this->helper->getView()->stepIndex);
        unset($this->helper->getView()->stepCount);
        $markup = $this->helper->__invoke();
        $this->assertRegExp('`<form method="POST"><fieldset><legend>Step fieldset</legend>.*</fieldset></form>`', $markup);
        $this->assertRegExp('`legend>(<b>MultipageFormRow content</b>){3}</fieldset`', $markup);
    }
    
    public function testRenderingWithStepIndexAttributeGeneratesFormAndStepIndexInformation()
    {
        unset($this->helper->getView()->stepCount);
        $markup = $this->helper->__invoke();
        $this->assertRegExp('`<h2>Étape 1</h2>.*<form`s', $markup);
    }
    
    public function testRenderingGeneratesFormAndStepInformation()
    {
        $markup = $this->helper->__invoke();
        $this->assertRegExp('`<h2>Étape 1 sur 3</h2>.*<form`s', $markup);
    }
}