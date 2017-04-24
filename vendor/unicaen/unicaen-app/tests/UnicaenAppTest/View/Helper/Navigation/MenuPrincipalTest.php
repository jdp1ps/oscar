<?php
namespace UnicaenAppTest\View\Helper\Navigation;

use UnicaenApp\View\Helper\Navigation\MenuPrincipal;
use Zend\Navigation\Navigation;

/**
 * Description of MenuPrincipalTest
 *
 * @property MenuPrincipal $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MenuPrincipalTest extends AbstractTest
{
    protected $navigation  = 'menu-principal/navigation.php';
    protected $routes      = 'menu-principal/routes.php';
    protected $helperClass = 'UnicaenApp\View\Helper\Navigation\MenuPrincipal';
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->routeMatch->setMatchedRouteName('contact/ajouter');
    }
    
    public function testReturnsSelfWhenInvoked()
    {
        $this->assertSame($this->helper, $this->helper->__invoke());
    }
    
    public function testAcceptsContainer()
    {
        $this->helper->__invoke($container = new Navigation());
        $this->assertSame($container, $this->helper->getContainer());
    }
    
    public function testCanRenderMenuFromServiceAlias()
    {
        $markup = $this->helper->render('Navigation');
        $this->assertEquals($this->getExpected('menu-principal/default.phtml'), $markup);
    }
}