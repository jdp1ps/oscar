<?php
namespace UnicaenAppTest\View\Helper\Navigation;

use UnicaenApp\View\Helper\Navigation\Plan;

/**
 * Description of PlanTest
 *
 * @property Plan $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class PlanTest extends AbstractTest
{
    protected $navigation  = 'plan/navigation.php';
    protected $routes      = 'plan/routes.php';
    protected $helperClass = 'UnicaenApp\View\Helper\Navigation\Plan';
    
    public function testCanRenderMenuFromServiceAlias()
    {
        $markup = $this->helper->render('Navigation');
        $this->assertEquals($this->getExpected('plan/default.phtml'), $markup);
    }
}