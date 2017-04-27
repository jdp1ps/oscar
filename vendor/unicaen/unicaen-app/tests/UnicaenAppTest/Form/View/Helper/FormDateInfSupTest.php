<?php
namespace UnicaenAppTest\Form\View\Helper;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Form\Element\DateInfSup;
use UnicaenApp\Form\View\Helper\FormDateInfSup;
use Zend\Form\View\HelperConfig;
use Zend\View\Renderer\PhpRenderer;

/**
 * Description of FormDateInfSupTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class FormDateInfSupTest extends PHPUnit_Framework_TestCase
{
    protected $helper;
    protected $renderer;

    public function setUp()
    {
        $this->helper = new FormDateInfSup();

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
        $element = new DateInfSup('elem');
        $markup = $helper($element);
        $this->assertInternalType('string', $markup);
    }
    
    public function testElementDateSupOptionsArePropagatedToHelper()
    {
        $helper = $this->helper;
        $element = new DateInfSup('elem');
        
        foreach (array(true, false) as $value) {
            $element->setDateSupActivated($value)
                    ->getInputFilter()->setDateSupRequired($value);
            $helper($element);
            $this->assertEquals(
                    $element->getDateSupActivated(), 
                    $this->readAttribute($helper, 'dateSupActivated'));
            $this->assertEquals(
                    $element->getInputFilter()->getDateSupRequired(), 
                    $this->readAttribute($helper, 'dateSupRequired')); 
        }
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\LogicException
     */
    public function testGettingJavascriptThrowsExceptionIfNoElementSet()
    {
        $this->helper->getJavascript();
    }
    
    public function testRenderingJavascript()
    {
        $element = new DateInfSup('elem');
        
        // render JS
        $this->helper->setRenderJs(true);
        $markup = $this->helper->render($element);
        $this->assertContains($this->helper->getJavascript(), $markup);
        
        // do NOT render JS
        $this->helper->setRenderJs(false);
        $headScriptHelper = $this->getMock('Zend\View\Helper\InlineScript', array('appendScript'));
        $this->renderer->getHelperPluginManager()->setAllowOverride(true)->setService('inlineScript', $headScriptHelper);
        $headScriptHelper->expects($this->atLeastOnce())
                         ->method('appendScript');
        $this->helper->render($element);
    }
    
    public function testRenderingGeneratesDivForEachDate()
    {
        $element = new DateInfSup('elem');
        
        $this->helper->setRenderJs(false);
        
        $element->setDateSupActivated(false);
        $markup = $this->helper->render($element);
        $this->assertRegexp('#<div class="input-dateinf .*">.+</div>#s', $markup);
        // NB: avec l'option "s", le métacaractère point (.) remplace n'importe quel caractère, y compris les nouvelles lignes
        
        $element->setDateSupActivated(true);
        $markup = $this->helper->render($element);
        $this->assertRegexp('#<div class="input-dateinf .*">.+</div>\s*<div class="input-datesup .*">.+</div>#s', $markup);
        // NB: avec l'option "s", le métacaractère point (.) remplace n'importe quel caractère, y compris les nouvelles lignes
    }
    
    public function testRenderingElementWithErrorsGeneratesDivWithErrorClass()
    {
        $element = new DateInfSup('elem');
        $element->setMessages(array('inf' => 'Erreur 1', 'sup' => 'Erreur 2'));
        
        $this->helper->setRenderJs(false);
        
        $markup = $this->helper->render($element);
        $this->assertRegexp('#<div class="input-dateinf.* error">.+</div>\s*<div class="input-datesup.* error">.+</div>#s', $markup);
    }
    
    public function testRenderingElementWithErrorsAppendsErrorClassToInput()
    {
        $element = new DateInfSup('elem');
        $element->setMessages(array('inf' => 'Erreur 1', 'sup' => 'Erreur 2'));
        
        $markup = $this->helper->render($element);
        $this->assertRegexp('#<input type="text" name="elem\[inf\]".* class=".* input-error".*>#', $markup);
        $this->assertRegexp('#<input type="text" name="elem\[sup\]".* class=".* input-error".*>#', $markup);
    }
    
    public function testRenderingGeneratesLabelAndInputForEachDate()
    {
        $element = new DateInfSup('elem');
        $element->setDateInfLabel('Date min')->setDateInf('01/01/2013');
        
        $this->helper->setRenderJs(false);
        
        $element->setDateSupActivated(false);
        $markup = $this->helper->render($element);
        $this->assertRegexp('#<label for="dateinfsup-inf-text-[a-zA-Z0-9]+">Date min</label>#', $markup);
        $this->assertRegexp('#<input type="text" name="elem\[inf\]".* id="dateinfsup-inf-text-[a-zA-Z0-9]+" class="input-dateinf required\s*" value="01/01/2013">#', $markup);
        
        $element->setDateSupActivated(true)->setDateSupLabel('Date max')->setDateSup('31/12/2013');
        $markup = $this->helper->render($element);
        $this->assertRegexp('#<label for="dateinfsup-sup-text-[a-zA-Z0-9]+">Date max</label>#', $markup);
        $this->assertRegexp('#<input type="text" name="elem\[sup\]".* id="dateinfsup-sup-text-[a-zA-Z0-9]+" class="input-datesup required\s*" value="31/12/2013">#', $markup);
    }
    
    public function testRenderingGeneratesTrashLinkOnlyWhenMaxDateIsNotRequired()
    {
        $element = new DateInfSup('elem');
        
        $element->getInputFilter()->setDateSupRequired(false);
        $markup = $this->helper->render($element);
        $this->assertRegexp('`<a id="dateinfsup-clear-sup-[a-zA-Z0-9]+" href="#" title="Vider">Vider</a>`', $markup);
        
        $element->getInputFilter()->setDateSupRequired(true);
        $markup = $this->helper->render($element);
        $this->assertNotRegexp('`<a id="dateinfsup-clear-sup" href="#" title="Vider">Vider</a>`', $markup);
    }
    
    public function testRenderingWithSameInputAttributesForBothDates()
    {
        $element = new DateInfSup('elem');
        $element->setDateInfLabel('Date min')->setDateInf(null)
                ->setDateSupActivated(true)->setDateSupLabel('Date max')->setDateInf(null);

        $element->setAttribute('class', 'common-input-class')
                ->setAttribute('title', 'common-input-title');
        $markup = $this->helper->render($element);
        $this->assertRegexp('#<input type="text" name="elem\[inf\]" title="common-input-title".* class="input-dateinf required common-input-class" value="">#', $markup);
        $this->assertRegexp('#<input type="text" name="elem\[sup\]" title="common-input-title".* class="input-datesup required common-input-class" value="">#', $markup);
    }
    
    public function testRenderingWithDifferentInputAttributesForDates()
    {
        $element = new DateInfSup('elem');
        $element->setDateInfLabel('Date min')->setDateInf(null)
                ->setDateSupActivated(true)->setDateSupLabel('Date max')->setDateInf(null);

        $element->setAttribute('class', array('min-input-class', 'max-input-class'))
                ->setAttribute('title', array('min-input-title', 'max-input-title'));
        $markup = $this->helper->render($element);
        $this->assertRegexp('#<input type="text" name="elem\[inf\]" title="min-input-title".* class="input-dateinf required min-input-class" value="">#', $markup);
        $this->assertRegexp('#<input type="text" name="elem\[sup\]" title="max-input-title".* class="input-datesup required max-input-class" value="">#', $markup);
    }
    
    public function testRenderingWithSameLabelAttributesForBothDates()
    {
        $element = new DateInfSup('elem');
        $element->setDateInfLabel('Date min')
                ->setDateSupActivated(true)->setDateSupLabel('Date max');

        $attributes = array(
            'title' => 'Common label title', 
            'class' => 'common-label-class'
        );
        $element->setLabelAttributes($attributes);
        $markup = $this->helper->render($element);
        $this->assertRegexp('#<label title="Common label title" class="common-label-class" for="dateinfsup-inf-text-[a-zA-Z0-9]+">Date min</label>#', $markup);
        $this->assertRegexp('#<label title="Common label title" class="common-label-class" for="dateinfsup-sup-text-[a-zA-Z0-9]+">Date max</label>#', $markup);
    }
    
    public function testRenderingWithDifferentLabelAttributesForDates()
    {
        $element = new DateInfSup('elem');
        $element->setDateInfLabel('Date min')
                ->setDateSupActivated(true)->setDateSupLabel('Date max');

        $this->helper->setRenderJs(false);
        
        $attributes = array(
            'title' => array('Min label title', 'Max label title'),
            'class' => array('min-label-class', 'max-label-class')
        );
        $element->setLabelAttributes($attributes);
        $markup = $this->helper->render($element);
        $this->assertRegexp('#<label title="Min label title" class="min-label-class" for="dateinfsup-inf-text-[a-zA-Z0-9]+">Date min</label>#', $markup);
        $this->assertRegexp('#<label title="Max label title" class="max-label-class" for="dateinfsup-sup-text-[a-zA-Z0-9]+">Date max</label>#', $markup);
    }
    
    public function testRenderingIncludingTimeOrNot()
    {
        $element = new DateInfSup('elem');
        $element->setDateInf('01/06/2013')
                ->setDateSup('21/06/2013');

        $element->setIncludeTime(true);
        $markup = $this->helper->render($element);
        $this->assertRegexp('#<input type="text" name="elem\[inf\]".* value="' . $element->getDateInfToString() . '">#', $markup);
        $this->assertRegexp('#<input type="text" name="elem\[sup\]".* value="' . $element->getDateSUpToString() . '">#', $markup);

        $element->setIncludeTime(false);
        $markup = $this->helper->render($element);
        $this->assertRegexp('#<input type="text" name="elem\[inf\]".* value="01/06/2013">#', $markup);
        $this->assertRegexp('#<input type="text" name="elem\[sup\]".* value="21/06/2013">#', $markup);
    }
    
    public function testRenderingWithHelperReadonlyOptionsActivated()
    {
        $element = new DateInfSup('elem');
        
        $this->helper->setDateInfReadonly(true)
                     ->setDateSupReadonly(true);
        $markup = $this->helper->render($element);
        $this->assertRegexp('#<input type="text" name="elem\[inf\]".* readonly="readonly".*>#', $markup);
        $this->assertRegexp('#<input type="text" name="elem\[sup\]".* readonly="readonly".*>#', $markup);
    }
}