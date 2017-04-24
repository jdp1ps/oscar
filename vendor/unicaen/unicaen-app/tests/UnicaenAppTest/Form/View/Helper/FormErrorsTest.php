<?php
namespace UnicaenAppTest\Form\View\Helper;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Form\View\Helper\FormErrors;
use Zend\Form\View\HelperConfig;
use Zend\View\Renderer\PhpRenderer;

/**
 * Description of FormErrorsTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class FormErrorsTest extends PHPUnit_Framework_TestCase
{
    protected $helper;
    protected $renderer;

    public function setUp()
    {
        $this->helper = new FormErrors();

        $this->renderer = new PhpRenderer;
        $helpers = $this->renderer->getHelperPluginManager();
        $config = new HelperConfig();
        $config->configureServiceManager($helpers);

        $this->helper->setView($this->renderer);
    }
    
    public function testInvokingWithNoArgReturnsPluginItSelf()
    {
        $helper = $this->helper;
        $this->assertSame($helper, $helper());
    }
    
    public function testInvokingWithElementReturnsString()
    {
        $helper = $this->helper;
        $form = new \Zend\Form\Form('form');
        $markup = $helper($form);
        $this->assertInternalType('string', $markup);
    }
    
    public function testInvokingSpecifyingMessageSetsMessageOption()
    {
        $helper = $this->helper;
        $form = new \Zend\Form\Form('form');
        $helper($form, $message = 'Oups!');
        $this->assertEquals($this->helper->getMessage(), $message);
    }
    
    public function testRenderingWithFormWithoutErrorsReturnsEmptyString()
    {
        $helper = $this->helper;
        $form = new \Zend\Form\Form('form');
        $markup = $helper($form);
        $this->assertEquals('', $markup);
    }
    
    public function testRenderingFormErrorsGeneratesDivContainer()
    {
        $form = new \Zend\Form\Form('form');
        $form->add($elem = new \Zend\Form\Element\Text('text'));
        $elem->setMessages(array('Erreur de saisie sur ce champ'));
        
        $markup = $this->helper->render($form);
        $this->assertRegexp('#<div class="alert alert-error">.*</div>#s', $markup);
        // NB: avec l'option "s", le métacaractère point (.) remplace n'importe quel caractère, y compris les nouvelles lignes
    }
    
    public function testRenderingFormErrorsGeneratesHeaderBeforeErrorsList()
    {
        $form = new \Zend\Form\Form('form');
        $form->add($elem = new \Zend\Form\Element\Text('text'));
        $elem->setMessages(array('Erreur de saisie sur ce champ'));
        
        $markup = $this->helper->render($form);
        $this->assertRegexp('#<strong>Attention!</strong>\s*<ul>#', $markup);
    }
    
    public function testCanTranslate()
    {
        $mockTranslator = $this->getMock('Zend\I18n\Translator\Translator');
        $mockTranslator->expects($this->any())
                       ->method('translate')
                       ->will($this->returnValue('translated content'));
        
        $form = new \Zend\Form\Form('form');
        $form->add($elem = new \Zend\Form\Element\Text('text'));
        $elem->setMessages(array('Erreur de saisie sur ce champ'));
        
        $this->helper->setTranslator($mockTranslator);
        $this->assertTrue($this->helper->hasTranslator());
        
        $this->helper->render($form);
    }
    
    public function testRenderingFormErrorsGeneratesErrorsList()
    {
        $form = new \Zend\Form\Form('form');
        $form->add($elem1 = new \Zend\Form\Element\Text('text'));
        $form->add($elem2 = new \Zend\Form\Element\Checkbox('agree'));
        $elem1->setMessages(array($msg1 = 'Erreur 1', $msg2 = 'Erreur 2'));
        $elem2->setMessages(array($msg3 = 'Erreur 3'));
        
        $markup = $this->helper->render($form);
        $this->assertRegexp('#<ul>\s*<li>Erreur 1</li>\s*<li>Erreur 2</li>\s*<li>Erreur 3</li>\s*</ul>#s', $markup);
        // NB: avec l'option "s", le métacaractère point (.) remplace n'importe quel caractère, y compris les nouvelles lignes
    }
}