<?php
namespace UnicaenAppTest\Form\View\Helper;

use UnicaenApp\Form\Element\DateInfSup;
use UnicaenApp\Form\View\Helper\FormRowDateInfSup;
use UnicaenAppTest\View\Helper\TestAsset\ArrayTranslatorLoader;
use Zend\Form\Form;
use Zend\Form\View\HelperConfig;
use Zend\I18n\Translator\Translator;
use Zend\View\Renderer\PhpRenderer;

/**
 * Description of FormRowDateInfSupTest
 *
 * @property FormRowDateInfSup $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class FormRowDateInfSupTest extends AbstractTest
{
    protected $helperClass = 'UnicaenApp\Form\View\Helper\FormRowDateInfSup';
    protected $delegateHelperClass;
    protected $renderer;

    public function setUp()
    {
        parent::setUp();
        
        $this->renderer = new PhpRenderer;
        $helpers = $this->renderer->getHelperPluginManager();
        $config = new HelperConfig();
        $config->configureServiceManager($helpers);

        $this->delegateHelperClass = $this->getMock('UnicaenApp\Form\View\Helper\FormDateInfSup', array('render'));
        $this->delegateHelperClass
                ->expects($this->any())
                ->method('render')
                ->will($this->returnValue('FormDateInfSup helper markup'));
        $helpers->setService('formDateInfSup', $this->delegateHelperClass);
        
        $this->helper->setView($this->renderer);
        $this->helper->setRenderJs(false);
    }
    
    /**
     * @expectedException \Zend\Form\Exception\InvalidArgumentException
     */
    public function testRenderingWithInvalidElementThrowsException()
    {
        $this->helper->render(new Form('form'));
    }
    
    public function testRenderingWihtoutLabelReturnsDelegateHelperMarkup()
    {
        $elem = new DateInfSup('elem');
        
        $markup = $this->helper->render($elem);
        $this->assertEquals('FormDateInfSup helper markup', $markup);
    }
    
    public function testRenderingWithLabelGeneratesFieldsetContainer()
    {
        $elem = new DateInfSup('elem');
        $elem->setLabel("Date de début et de fin");
        
        $markup = $this->helper->render($elem);
        $this->assertRegExp('`<fieldset class="dateinfsup"><legend>Date de début et de fin</legend>FormDateInfSup helper markup</fieldset>`s', $markup);
    }
    
    public function testCanTranslateLabel()
    {
        $elem = new DateInfSup('elem');
        $elem->setDateInf('15/07/2013')->setLabel("Date de début et de fin");
        
        $this->helper->setTranslator($this->_getTranslator());
        $markup = $this->helper->render($elem);
        $this->assertEquals($this->getExpected('form-row-date-inf-sup/default.phtml'), $markup);
    }
    
    public function getMessagesInDifferentFormat()
    {
        return array(
            array(array('Erreur 1', 'Erreur 2')),
            array(array('inf' => 'Erreur 1', 'sup' => 'Erreur 2')),
            array(array('inf' => array('Erreur 1', 'Erreur 2'), 'sup' => 'Erreur 2')),
            array(array('inf' => array('Erreur 1', 'Erreur 2'), 'sup' => array('Erreur 3', 'Erreur 4'))),
        );
    }
    
    /**
     * @dataProvider getMessagesInDifferentFormat
     * @param array $messages
     */
    public function testRenderingElementWithErrorsInDifferentFormatGeneratesErrors($messages)
    {
        $elem = new DateInfSup('elem');
        
        $elem->setMessages($messages);
        $markup = $this->helper->render($elem);
        $this->assertRegExp('`<ul>(<li>Erreur \d</li>)+</ul>`', $markup);
    }
    
    /**
     * @dataProvider getMessagesInDifferentFormat
     * @param array $messages
     */
    public function testRenderingElementWithErrorsDoesNotGeneratesErrorsIfNotAsked($messages)
    {
        $elem = new DateInfSup('elem');
        
        $this->helper->setRenderErrors(false);
        
        $elem->setMessages($messages);
        $markup = $this->helper->render($elem);
        $this->assertNotRegExp('`<ul>(<li>Erreur \d</li>)+</ul>`', $markup);
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
            "Date de début et de fin" => "Start and end dates",
        );

        $translator = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);
        
        return $translator;
    }
}