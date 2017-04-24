<?php

namespace UnicaenAppTest\Form\View\Helper;

use UnicaenApp\Form\Element\MultipageFormNav as MultipageFormNavElement;
use UnicaenApp\Form\View\Helper\MultipageFormNav;
use UnicaenAppTest\View\Helper\TestAsset\ArrayTranslatorLoader;
use Zend\Form\View\HelperConfig;
use Zend\I18n\Translator\Translator;
use Zend\View\Renderer\PhpRenderer;

/**
 * Description of MultipageFormNavTest
 *
 * @property MultipageFormNav $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MultipageFormNavTest extends AbstractTest
{
    protected $helperClass = 'UnicaenApp\Form\View\Helper\MultipageFormNav';
    protected $renderer;
    protected $element;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->renderer = new PhpRenderer;
        $helpers = $this->renderer->getHelperPluginManager();
        $config = new HelperConfig();
        $config->configureServiceManager($helpers);
        
        $this->helper->setView($this->renderer);
        
        $this->element = new MultipageFormNavElement('nav');
    }
    
    public function testInvokingReturnsString()
    {
        $this->assertInternalType('string', $this->helper->__invoke($this->element));
    }
    
    public function testRenderingDefault()
    {
        $markup = $this->helper->__invoke($this->element);
        $this->assertEquals($this->getExpected('multipage-form-nav/default.phtml'), $markup);
        
        // traduction
        $this->helper->setTranslator($this->_getTranslator());
        $markup = $this->helper->__invoke($this->element);
        $this->assertEquals($this->getExpected('multipage-form-nav/default_t.phtml'), $markup);
    }
    
    public function testRenderingGeneratesPreviousButton()
    {
        $this->element->setActivatePrevious(true);
        $markup = $this->helper->__invoke($this->element);
        $this->assertEquals($this->getExpected('multipage-form-nav/previous.phtml'), $markup);
    }
    
    public function testRenderingGeneratesNextButton()
    {
        $this->element->setActivateNext(true);
        $markup = $this->helper->__invoke($this->element);
        $this->assertEquals($this->getExpected('multipage-form-nav/next.phtml'), $markup);
    }
    
    public function testRenderingGeneratesNextButtonBeforePreviousOne()
    {
        // NB: ordre permettant de valider avec la touche "Entrée"
        $this->element->setActivatePrevious(true)
                      ->setActivateNext(true);
        $markup = $this->helper->__invoke($this->element);
        $this->assertEquals($this->getExpected('multipage-form-nav/previous-after-next.phtml'), $markup);
    }
    
    public function testRenderingGeneratesSubmitButton()
    {
        $this->element->setActivateSubmit(true);
        $markup = $this->helper->__invoke($this->element);
        $this->assertEquals($this->getExpected('multipage-form-nav/submit.phtml'), $markup);
    }
    
    public function testRenderingGeneratesCancelButton()
    {
        $this->element->setActivateCancel(true);
        $markup = $this->helper->__invoke($this->element);
        $this->assertEquals($this->getExpected('multipage-form-nav/cancel.phtml'), $markup);
    }
    
    public function testRenderingGeneratesConfirmButton()
    {
        $this->element->setActivateConfirm(true);
        $markup = $this->helper->__invoke($this->element);
        $this->assertEquals($this->getExpected('multipage-form-nav/confirm.phtml'), $markup);
    }
    
    public function testRenderingGeneratesSubmitButtonBeforeNextOne()
    {
        // NB: ordre permettant de valider (terminer) avec la touche "Entrée"
        $this->element->setActivatePrevious(true)
                      ->setActivateSubmit(true);
        $markup = $this->helper->__invoke($this->element);
        $this->assertEquals($this->getExpected('multipage-form-nav/next-after-submit.phtml'), $markup);
    }
    
    public function testRenderingDoesNotGenerateConfirmButtonIfSubmitOneIs()
    {
        $this->element->setActivateSubmit(true)
                      ->setActivateConfirm(true);
        $markup = $this->helper->__invoke($this->element);
        $this->assertEquals($this->getExpected('multipage-form-nav/submit-or-confirm.phtml'), $markup);
    }

    /**
     * Returns translator
     *
     * @return Translator
     */
    protected function _getTranslator()
    {
        $loader = new ArrayTranslatorLoader();
        $loader->translations = array(
            "Passer à l'étape suivante"           => "Go to next step",
            "Suivant >"                           => "Next >",
            "Abandonner définitivement la saisie" => "Cancel it all",
            "Annuler"                             => "Cancel",
            "Terminer"                            => "Finish",
            "Confirmer et enregistrer"            => "Confirm and save",
        );

        $translator = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);
        
        return $translator;
    }
}