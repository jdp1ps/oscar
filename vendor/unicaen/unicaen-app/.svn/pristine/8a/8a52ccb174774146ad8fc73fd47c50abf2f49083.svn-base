<?php
namespace UnicaenAppTest\View\Helper\Navigation;

/**
 * Description of FilArianeTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see \UnicaenApp\View\Helper\Navigation\FilAriane
 */
class FilArianeTest extends AbstractTest
{
    protected $navigation  = 'fil-ariane/navigation.php';
    protected $routes      = 'fil-ariane/routes.php';
    protected $helperClass = 'UnicaenApp\View\Helper\Navigation\FilAriane';
    
    public function testConstructorInitializesProperties()
    {
        $this->assertTrue($this->helper->getRenderInvisible());
        $this->assertEquals(0, $this->helper->getMinDepth());
    }
    
    public function testRenderingWrapsMarkupInHtmlList()
    {
        $markup = $this->helper->render('Navigation');
        $this->assertStringStartsWith('<ul class="breadcrumb"><li>', $markup);
        $this->assertStringEndsWith('</li></ul>', $markup);
    }
}