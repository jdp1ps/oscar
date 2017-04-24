<?php
namespace UnicaenAppTest\Form\View\Helper;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Form\Element\DateInfSup;
use UnicaenApp\Form\Element\MultipageFormNav;
use UnicaenApp\Form\View\Helper\MultipageFormRow;
use Zend\Form\View\HelperConfig;
use Zend\View\Renderer\PhpRenderer;

/**
 * Description of MultipageFormRowTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MultipageFormRowTest extends PHPUnit_Framework_TestCase
{    
    protected $helper;
    protected $renderer;

    public function setUp()
    {
        $this->helper = new MultipageFormRow();

        $this->renderer = new PhpRenderer;
        $helpers = $this->renderer->getHelperPluginManager();
        $config = new HelperConfig();
        $config->configureServiceManager($helpers);

        $this->helper->setView($this->renderer);
    }
    
    public function testRenderMethodDelegatesRenderingToSpecificViewHelperWhenSpecificElementSpecified()
    {
        // MultipageFormNav element
        $multipageFormNavHelper = $this->getMock('UnicaenApp\Form\View\Helper\MultipageFormNav');
        $this->renderer->getHelperPluginManager()->setService('multipageFormNav', $multipageFormNavHelper);
        $element = new MultipageFormNav('elem');
        $multipageFormNavHelper->expects($this->once())
                               ->method('__invoke')
                               ->with($element)
                               ->will($this->returnValue($html = 'html content'));
        $this->assertEquals($html, $this->helper->render($element));
        
        // DateInfSup element
        $formRowDateInfSupHelper = $this->getMock('UnicaenApp\Form\View\Helper\FormDateInfSup');
        $this->renderer->getHelperPluginManager()->setService('formRowDateInfSup', $formRowDateInfSupHelper);
        $element = new DateInfSup('elem');
        $formRowDateInfSupHelper->expects($this->once())
                                ->method('__invoke')
                                ->with($element)
                                ->will($this->returnValue($html = 'html content'));
        $this->assertEquals($html, $this->helper->render($element));
    }
    
    public function testRenderMethodDelegatesRenderingToSpecificViewHelperWhenStandardElementSpecified()
    {
        $multipageFormNavHelper  = $this->getMock('UnicaenApp\Form\View\Helper\MultipageFormNav');
        $formRowDateInfSupHelper = $this->getMock('UnicaenApp\Form\View\Helper\FormDateInfSup');
        $this->renderer->getHelperPluginManager()->setService('multipageFormNav',  $multipageFormNavHelper);
        $this->renderer->getHelperPluginManager()->setService('formRowDateInfSup', $formRowDateInfSupHelper);
        
        $element = new \Zend\Form\Element('elem');
        $multipageFormNavHelper->expects($this->never())
                               ->method('__invoke');
        $formRowDateInfSupHelper->expects($this->never())
                                ->method('__invoke');
        $this->helper->render($element);
    }
}