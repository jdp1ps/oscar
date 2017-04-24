<?php
namespace UnicaenAppTest\View\Helper\Navigation;

use UnicaenApp\View\Helper\Navigation\MenuPiedDePage;

/**
 * Description of MenuPiedDePageTest
 *
 * @property MenuPiedDePage $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MenuPiedDePageTest extends AbstractTest
{
    protected $navigation  = 'menu-pied-de-page/navigation.php';
    protected $routes      = 'menu-pied-de-page/routes.php';
    protected $helperClass = 'UnicaenApp\View\Helper\Navigation\MenuPiedDePage';
    
    public function testRenderingNullContainerUseCurrentContainer()
    {
        $container = $this->getMock('Zend\Navigation\Navigation', array('findAllBy'));
        $container->expects($this->once())
                  ->method('findAllBy')
                  ->will($this->returnValue(array()));
        
        $this->helper->setContainer($container)
                     ->render(null);
    }
    
    public function testReturnsEmptyStringIfNoPageWithFooterAttributeFound()
    {
        $this->container->findOneBy('id', 'apropos')->set('footer', false);
        $this->container->findOneBy('id', 'etab')->set('footer', false);
        $this->assertEquals('', $this->helper->render($this->container));
    }
    
    public function testReturnsCorrectMarkup()
    {
        $markup = $this->helper->render('Navigation');
        $this->assertEquals($this->getExpected('menu-pied-de-page/default.phtml'), $markup);
    }
}