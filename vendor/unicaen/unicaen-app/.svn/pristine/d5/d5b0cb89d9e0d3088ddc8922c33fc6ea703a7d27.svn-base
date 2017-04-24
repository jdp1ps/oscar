<?php
namespace UnicaenAppTest\View\Helper\Navigation;

use UnicaenApp\View\Helper\Navigation\MenuSecondaire;
use Zend\Navigation\Navigation;

/**
 * Description of MenuSecondaireTest
 *
 * @property MenuSecondaire $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MenuSecondaireTest extends AbstractTest
{
    protected $navigation  = 'menu-secondaire/navigation.php';
    protected $routes      = 'menu-secondaire/routes.php';
    protected $helperClass = 'UnicaenApp\View\Helper\Navigation\MenuSecondaire';
    
    public function testReturnsSelfWhenInvoked()
    {
        $this->assertSame($this->helper, $this->helper->__invoke());
    }
    
    public function testAcceptsContainer()
    {
        $this->helper->__invoke($container = new Navigation());
        $this->assertSame($container, $this->helper->getContainer());
    }
    
    public function testRenderingAcceptsNullContainer()
    {
        $this->assertEquals('', $this->helper->renderMenu(null));
    }
    
    public function testCanRenderMenuFromServiceAlias()
    {
        $this->routeMatch->setMatchedRouteName('contact'); // niveau 2
        $markup = $this->helper->renderMenu('Navigation');
        $this->assertEquals($this->getExpected('menu-secondaire/contact-active.phtml'), $markup);
    }
    
    public function getMatchedRouteNameAndExpectedScript()
    {
        return array(
            'niv-1-insuffisant' => array('home',                            'menu-secondaire/home-active.phtml'),
            'niv-2-sans-filles' => array('apropos',                         'menu-secondaire/apropos-active.phtml'),
            'niv-2-avec-filles' => array('contact',                         'menu-secondaire/contact-active.phtml'),
            'niv-3-avec-filles' => array('contact/ajouter',                 'menu-secondaire/contact-ajouter-active.phtml'),
            'niv-4-sans-filles' => array('contact/ajouter/adresse',         'menu-secondaire/contact-ajouter-adresse-active.phtml'),
            'niv-4-avec-filles' => array('contact/ajouter/adresse/postale', 'menu-secondaire/contact-ajouter-adresse-postale-active.phtml'),
        );
    }
    
    /**
     * @dataProvider getMatchedRouteNameAndExpectedScript
     * @param string $matchedRouteName
     * @param string $script
     */
    public function testRenderingReturnsCorrectMarkup($matchedRouteName, $script)
    {
        $this->routeMatch->setMatchedRouteName($matchedRouteName);
        $markup = $this->helper->renderMenu('Navigation');
        $this->assertEquals($this->getExpected($script), $markup);
    }
}