<?php

namespace UnicaenAppTest\Form\View\Helper;

//use DOMDocument;
use UnicaenApp\Form\View\Helper\MultipageFormRecap;
use UnicaenAppTest\Form\TestAsset\ContactMultipageForm;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Form\View\HelperConfig;
use Zend\View\Exception\InvalidArgumentException;
use Zend\View\Renderer\PhpRenderer;

/**
 * Description of MultipageFormNavTest
 *
 * @property MultipageFormRecap $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MultipageFormRecapTest extends AbstractTest
{
//    protected $xml;
    protected $form;
    protected $helperClass = 'UnicaenApp\Form\View\Helper\MultipageFormRecap';
    protected $renderer;
    
    public function setUp()
    {
        parent::setUp();

        $this->renderer = new PhpRenderer;
        $helpers = $this->renderer->getHelperPluginManager();
        $config = new HelperConfig();
        $config->configureServiceManager($helpers);

        $mockMultipageFormRow = $this->getMock('\UnicaenApp\Form\View\Helper\MultipageFormRow');
        $mockMultipageFormRow->expects($this->any())
                             ->method('__invoke')
                             ->withAnyParameters()
                             ->will($this->returnValue('MultipageFormNav markup'));
        $helpers->setService('multipageFormRow', $mockMultipageFormRow);
        
        $this->helper->setView($this->renderer);
        
        $this->form = new ContactMultipageForm('form');
        $this->form->prepareElements();
        $this->helper->getView()->form = $this->form;
        
//        $this->xml = new DOMDocument();
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvokingBeforeSettingFormThrowsException()
    {
        unset($this->helper->getView()->form);
        $this->helper->__invoke();
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvokingWithInvalidFormTypeThrowsException()
    {
        $this->helper->getView()->form = new Element('elem');
        $this->helper->__invoke();
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvokingWithEmptyFormThrowsException()
    {
        $this->helper->getView()->form = new Form(); // no fieldset!
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
    
    public function testLabelsAndValuesAreAskedToFieldsetsImplementingMultipageFormFieldsetInterface()
    {
        $fieldset = $this->getMock('UnicaenAppTest\Form\TestAsset\IdentiteFieldset', array('getLabelsAndValues'));
        $fieldset->setName('fs');
        $form = new ContactMultipageForm(); // do not call prepareElements()!
        $form->add($fieldset);
        $this->helper->getView()->form = $form;
        
        $labelsAndValues = array(
            'nom' => array(
                'label' => "Nom", 
                'value' => "Paul Hochon",
            ),
            'mcb' => array(
                'label' => "Mcb", 
                'value' => array("Melle"),
            ),
        );
        $fieldset->expects($this->once())
                 ->method('getLabelsAndValues')
                 ->will($this->returnValue($labelsAndValues));
        
        $this->helper->__invoke();
    }
    
    public function testRenderingWhenNoLabelsAndValuesAreProvidedByFieldsetsImplementingMultipageFormFieldsetInterface()
    {
        $fieldset = $this->getMock('UnicaenAppTest\Form\TestAsset\IdentiteFieldset', array('getLabelsAndValues'));
        $fieldset->setName('fs');
        $form = new ContactMultipageForm(); // do not call prepareElements()!
        $form->add($fieldset);
        $this->helper->getView()->form = $form;
        
        $fieldset->expects($this->once())
                 ->method('getLabelsAndValues')
                 ->will($this->returnValue(array()));
        
        $markup = $this->helper->__invoke();
        $this->assertEquals($this->getExpected('multipage-form-recap/empty.phtml'), $markup);
    }
    
    public function testGeneratesCorrectMarkup()
    {
        $this->form->setValue(array(
            'identite' => array(
                'nom' => "Hochon",
                'prenom' => "Paul",
                'civ' => array("M"),
            ),
            'adresse' => array(
                'email' => "paul.hochon@mail.fr",
            ),
            'message' => array(
                'message' => "Coucou!",
            ),
        ));
        
        $markup = $this->helper->__invoke();
        $this->assertEquals($this->getExpected('multipage-form-recap/default.phtml'), $markup);
        
//        $this->assertRegExp('`^\s*<form method="POST">(.*?)</form>\s*$`s', $markup);
//        // NB: avec l'option "s", le métacaractère point (.) remplace n'importe quel caractère, y compris une nouvelle ligne
//        
//        $this->xml->loadHTML($markup);
//        $this->assertSelectCount('form', 1, $this->xml);
//        $this->assertSelectCount('form fieldset', $n = 3, $this->xml); // 3 fieldsets dans le formulaire
//        $this->assertSelectCount('form fieldset legend', $n, $this->xml);
//        $this->assertSelectCount('form fieldset dl', $n, $this->xml);
//        $this->assertSelectCount('form fieldset dl dt', 5, $this->xml); // 3 + 1 + 1 éléments dans les fieldsets
//        $this->assertSelectCount('form fieldset dl dd', 5, $this->xml);
//        
//        // verifie que le rendu de l'aide de vue MultipageFormRow se trouve en fin de formulaire
//        $this->assertRegExp('`</fieldset>\s*' . self::MULTIPAGE_FORM_ROW_MOCK_MARKUP . '\s*</form>`', $markup);
    }
}