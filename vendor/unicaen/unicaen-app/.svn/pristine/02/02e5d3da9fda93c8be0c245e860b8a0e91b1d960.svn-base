<?php
namespace UnicaenAppTest\Form\View\Helper;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Form\Element\LdapPeople;
use UnicaenApp\Form\View\Helper\FormLdapPeople;
use Zend\Form\Form;
use Zend\Form\View\HelperConfig;
use Zend\View\Renderer\PhpRenderer;

/**
 * Description of FormLdapPeople
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class FormLdapPeopleTest extends PHPUnit_Framework_TestCase
{
    protected $helper;
    protected $renderer;

    public function setUp()
    {
        $this->helper = new FormLdapPeople();

        $this->renderer = new PhpRenderer;
        $helpers = $this->renderer->getHelperPluginManager();
        $config = new HelperConfig();
        $config->configureServiceManager($helpers);

        $this->helper->setView($this->renderer);
    }
    
    public function testCanSetAutocompleteSource()
    {
        $autocompleteSource = '/autocomplete/source';
        $this->helper->setAutocompleteSource($autocompleteSource);
        $this->assertEquals($autocompleteSource, $this->helper->getAutocompleteSource());
    }
    
    public function testCanSetAutocompleteMinLength()
    {
        $autocompleteMinLength = 2;
        $this->helper->setAutocompleteMinLength($autocompleteMinLength);
        $this->assertEquals($autocompleteMinLength, $this->helper->getAutocompleteMinLength());
    }
    
    public function testCanSetSpinnerSource()
    {
        $spinnerSource = '/spinner/source.gif';
        $this->helper->setSpinnerSource($spinnerSource);
        $this->assertEquals($spinnerSource, $this->helper->getSpinnerSource());
    }
    
    public function testInvokingWithNoArgReturnsPluginItSelf()
    {
        $helper = $this->helper;
        $this->assertSame($helper, $helper());
    }
    
    /**
     * @expectedException Zend\Form\Exception\InvalidElementException
     */
    public function testInvokingWithInvalidElementThrowsException()
    {
        $helper = $this->helper;
        $form = new Form('form');
        $helper($form);
    }
    
    public function testInvokingWithElementReturnsString()
    {
        $helper = $this->helper;
        $form = new LdapPeople('form');
        $markup = $helper($form);
        $this->assertInternalType('string', $markup);
    }
    
    /**
     * @expectedException Zend\Form\Exception\InvalidElementException
     */
    public function testRenderingInvalidElementThrowsException()
    {
        $form = new Form('form');
        $this->helper->render($form);
    }
    
    public function testRenderingElementWithoutDomIdCreatesOne()
    {
        $elem = new LdapPeople('elem');
        $this->helper->render($elem);
        $this->assertNotNull($elem->getAttribute('id'));
    }
    
    public function testRenderingTransformsElementNameIntoAnArrayWithIdKey()
    {
        $elem = new LdapPeople('elem');
        $name = $elem->getName();
        $this->helper->render($elem);
        $this->assertEquals($name . "[id]", $elem->getName());
    }
    
    public function testRenderingGeneratesAutocompleteInput()
    {
        $elem = new LdapPeople('elem');
        $elem->setAttribute('id', 'test-id');
        $markup = $this->helper->render($elem);
        $this->assertRegExp('`<input name="elem\[id\]" id="test-id" class="sas" type="text" value=""><input type="text" name="elem\[label\]" id="test-id-autocomplete" class="form-control input-sm" value="">`', $markup);
    }
    
    public function testRendersJavascript()
    {
        $elem = new LdapPeople('elem');
        $elem->setAttribute('id', 'test-id');
        $markup = $this->helper->render($elem);
        $this->assertContains('<script', $markup);
        $this->assertContains('</script>', $markup);
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\LogicException
     */
    public function testGettingJavascriptThrowsExceptionIfNoElementSet()
    {
        $this->helper->getJavascript();
    }
}