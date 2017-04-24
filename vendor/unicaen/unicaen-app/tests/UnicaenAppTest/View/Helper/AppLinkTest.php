<?php
namespace UnicaenAppTest\View\Helper;

use Zend\I18n\Translator\Translator;

/**
 * Description of AppLinkText
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AppLinkText extends AbstractTest
{
    protected $helperClass = 'UnicaenApp\View\Helper\AppLink';
    protected $router;
    protected $routeMatch;
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->router = $this->getMock('\Zend\Mvc\Router\SimpleRouteStack', array('assemble'));
        
        $this->routeMatch = new \Zend\Mvc\Router\RouteMatch(array());
        
        $this->helper->setTranslator(new Translator())
                     ->setRouter($this->router)
                     ->setRouteMatch($this->routeMatch)
                     ->setHomeRouteName('home');
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\LogicException
     */
    public function testThrowsExceptionIfNoTitleSpecified()
    {
        $this->helper->__invoke("");
    }
    
    public function testRendersCorrectMarkupWhenMatchedRouteIsNotHome()
    {
        $this->routeMatch->setMatchedRouteName('not-home');
        $this->router->expects($this->any())
                     ->method('assemble')
                     ->will($this->returnValue('/appli'));
        
        $markup = $this->helper->__invoke("Mon application");
        $expected = <<<EOS
<a class="navbar-brand" href="/appli" title="Page d'accueil de l'application"><h1 class="title">Mon application</h1></a>
EOS;
        $this->assertEquals($expected, $markup);
        
        $markup = $this->helper->__invoke("Mon application", "Magnifique appli!");
        $expected = <<<EOS
<a class="navbar-brand" href="/appli" title="Page d'accueil de l'application"><h1 class="title">Mon application<span>Magnifique appli!</span></h1></a>
EOS;
        $this->assertEquals($expected, $markup);
    }
    
    public function testRendersCorrectMarkupWhenMatchedRouteIsHome()
    {
        $this->routeMatch->setMatchedRouteName('home');
        $this->router->expects($this->never())
                     ->method('assemble');
        
        $markup = $this->helper->__invoke("Mon application");
        $expected = <<<EOS
<a class="navbar-brand"><h1 class="title">Mon application</h1></a>
EOS;
        $this->assertEquals($expected, $markup);
        
        $markup = $this->helper->__invoke("Mon application", "Magnifique appli!");
        $expected = <<<EOS
<a class="navbar-brand"><h1 class="title">Mon application<span>Magnifique appli!</span></h1></a>
EOS;
        $this->assertEquals($expected, $markup);
    }
}