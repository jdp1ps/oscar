<?php
namespace UnicaenAppTest\View\Helper\Navigation;

use UnicaenApp\View\Helper\Navigation\MenuContextuel;
use Zend\Navigation\Navigation;

/**
 * Description of MenuContextuelTest
 *
 * @property MenuContextuel $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MenuContextuelTest extends AbstractTest
{
    protected $navigation  = 'menu-contextuel/navigation.php';
    protected $routes      = 'menu-contextuel/routes.php';
    protected $helperClass = 'UnicaenApp\View\Helper\Navigation\MenuContextuel';
    
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
    
    public function testCanRenderFromServiceAlias()
    {
        $markup = $this->helper->render('Navigation');
        $this->assertEquals($this->getExpected('menu-contextuel/default.phtml'), $markup);
    }
    
    public function testHelperEntryPointWithoutAnyParams()
    {
        $returned = $this->helper->__invoke();
        $this->assertSame($this->helper, $returned);
        $this->assertSame($this->container, $returned->getContainer());
    }
    
    public function testCanRenderWithoutAnyParams()
    {
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/default.phtml'), $markup);
    }
    
    public function getIncludeOrExcudeMethod()
    {
        return array(
            array('includeIf', true),
            array('exceptIf', true),
            array('except',   null),
        );
    }
    
    /**
     * @dataProvider getIncludeOrExcudeMethod
     * @expectedException \UnicaenApp\Exception\LogicException
     * @param string $method
     * @param boolean $condition
     */
    public function testIncludeOrExcludeThrowsExceptionWhenNoParamSpecified($method, $condition)
    {
        $this->helper->$method($condition);
        $this->helper->render();
    }
    
    public function testRendersErrorMessageIfActivePageNotFound()
    {
        $this->routeMatch->setMatchedRouteName(null);
        $markup = $this->helper->render();
        $this->assertEquals("Impossible d'afficher les liens de navigation: Page de navigation active introuvable.", $markup);
    }
    
    public function testCanExcludePageByRoute()
    {
        $this->helper->exceptRoute('contact/ajouter/adresse');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/exclude-route-action.phtml'), $markup);
    }
    
    public function testCanExcludePageByRouteWithCondition()
    {
        // condition fausse
        $this->helper->exceptRouteIf(false, 'contact/ajouter/adresse');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/default.phtml'), $markup);
        
        // condition vraie
        $this->helper->exceptRouteIf(true, 'contact/ajouter/adresse');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/exclude-route-action.phtml'), $markup);
    }
    
    public function testCanExcludePageByAction()
    {
        $this->helper->except('ajouter-adresse');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/exclude-route-action.phtml'), $markup);
    }
    
    public function testCanExcludePageByActionWithCondition()
    {
        // condition fausse
        $this->helper->exceptIf(false, 'ajouter-adresse');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/default.phtml'), $markup);
        
        // condition vraie
        $this->helper->exceptIf(true, 'ajouter-adresse');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/exclude-route-action.phtml'), $markup);
    }
    
    public function testCanExcludePageByController()
    {
        $this->helper->except(null, 'contact-other');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/exclude-controller.phtml'), $markup);
    }
    
    public function testCanExcludePageByControllerWithCondition()
    {
        // condition fausse
        $this->helper->exceptIf(false, null, 'contact-other');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/default.phtml'), $markup);
        
        // condition vraie
        $this->helper->exceptIf(true, null, 'contact-other');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/exclude-controller.phtml'), $markup);
    }
    
    public function testCanExcludePageByParams()
    {
        $this->helper->except(null, null, array('source' => 'ldap', 'branch' => 'people'));
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/exclude-params.phtml'), $markup);
    }
    
    public function testCanExcludePageByParamsWithCondition()
    {
        // condition fausse
        $this->helper->exceptIf(false, null, null, array('source' => 'ldap', 'branch' => 'people'));
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/default.phtml'), $markup);
        
        // condition vraie
        $this->helper->exceptIf(true, null, null, array('source' => 'ldap', 'branch' => 'people'));
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/exclude-params.phtml'), $markup);
    }
    
    public function testCanIncludePageByRouteWithCondition()
    {
        // condition fausse
        $this->helper->includeRouteIf(false, 'contact/ajouter/adresse');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/default.phtml'), $markup);
        
        // condition vraie
        $this->helper->includeRouteIf(true, 'contact/ajouter/adresse');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/include-route-action.phtml'), $markup);
    }
    
    public function testCanIncludePageByActionWithCondition()
    {
        // condition fausse
        $this->helper->includeIf(false, 'ajouter-adresse');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/default.phtml'), $markup);
        
        // condition vraie
        $this->helper->includeIf(true, 'ajouter-adresse');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/include-route-action.phtml'), $markup);
    }
    
    public function testCanIncludePageByControllerWithCondition()
    {
        // condition fausse
        $this->helper->includeIf(false, null, 'contact-other');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/default.phtml'), $markup);
        
        // condition vraie
        $this->helper->includeIf(true, null, 'contact-other');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/include-controller.phtml'), $markup);
    }
    
    public function testCanIncludePageByParamsWithCondition()
    {
        // condition fausse
        $this->helper->includeIf(false, null, null, array('source' => 'ldap', 'branch' => 'people'));
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/default.phtml'), $markup);
        
        // condition vraie
        $this->helper->includeIf(true, null, null, array('source' => 'ldap', 'branch' => 'people'));
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/include-params.phtml'), $markup);
    }
    
    public function testCanFilterOnPagePropertyPresence()
    {
        $this->helper->withProp('class');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/with-prop.phtml'), $markup);
    }
    
    public function testCanFilterOnPagePropertyValue()
    {
        $this->helper->withProp('class', 'step1');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/with-prop-value.phtml'), $markup);
    }
    
    public function testCanFilterOnPagePropertyAbsence()
    {
        $this->helper->withoutProp('class');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/without-prop.phtml'), $markup);
    }
    
    public function testCanFilterOnPageParamPresence()
    {
        $this->helper->withParam('branch');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/with-param.phtml'), $markup);
    }
    
    public function testCanFilterOnPageParamAndSubstituteValue()
    {
        $this->helper->withParam('branch', 'deactivated');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/with-param-value.phtml'), $markup);
    }
    
    public function testCanFilterOnPageParamAbsence()
    {
        $this->helper->withoutParam('branch');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/without-param.phtml'), $markup);
    }
    
    public function testCanFilterOnPageTargetPresence()
    {
        $this->helper->withTarget();
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/with-target.phtml'), $markup);
    }
    
    public function getTarget()
    {
        return array(
            'integer'           => array(12),
            'string'            => array('12'),
            'object-without-id' => array(new TargetWithoutId()),
            'object-with-id'    => array(new TargetWithId()),
        );
    }
    
    /**
     * @dataProvider getTarget
     */
    public function testCanFilterOnPageTargetValue($target)
    {
        $this->helper->withTarget($target);
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/with-target-value.phtml'), $markup);
    }
    
    public function testCanFilterOnPageTargetAbsence()
    {
        $this->helper->withoutTarget();
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/without-target.phtml'), $markup);
    }
    
    public function testFilteringOnPageTargetAbsenceClearsPreviouslyMergedParam()
    {
        $this->helper->addParam('id', 123);
        $this->helper->withoutTarget();
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/without-target.phtml'), $markup);
    }
    
    public function testCanAddParamOnPagesHavingSegmentRoute()
    {
        $this->helper->addParam('source', 'db');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/add-param.phtml'), $markup);
    }
    
    public function testCanAddParamsOnPagesHavingSegmentRoute()
    {
        $this->helper->addParams(array('source' => 'db', 'branch' => 'user'));
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/add-params.phtml'), $markup);
    }
    
    public function testCanRemoveParamOnPagesHavingSegmentRoute()
    {
        $this->helper->removeParam('branch');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/remove-param.phtml'), $markup);
    }
    
    public function testCanAddCustomNonRenderedPropertiesOnPage()
    {
        $this->helper->addProp('iconify', true);
        $this->helper->addProps(array('prop1' => 'value1', 'prop2' => 'value2'));
        foreach ($this->helper->getPages() as $page) { /* @var $page \Zend\Navigation\Page\AbstractPage */
            $this->assertEquals(true, $page->get('iconify'));
            $this->assertEquals('value1', $page->get('prop1'));
            $this->assertEquals('value2', $page->get('prop2'));
        }
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/add-props.phtml'), $markup);
    }
    
    public function testAddingExistingPropertyOnPageConcatenateValues()
    {
        $this->helper->addProp('custom', 'bar');
        foreach ($this->helper->getPages() as $page) { /* @var $page \Zend\Navigation\Page\AbstractPage */
            $this->assertEquals('foo bar', $page->get('custom'));
        }
    }
    
    public function testCanIconifyPagesHavingRequiredClassAndIconProperties()
    {
        $this->routeMatch->setMatchedRouteName('contact');
        $this->helper->withTarget(12);
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/iconify.phtml'), $markup);
    }
    
    public function testCanRenderModalMarkup()
    {
        $this->routeMatch->setMatchedRouteName('contact');
        $this->helper->withTarget(12)->setModal(true);
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/modal.phtml'), $markup);
//        $pattern  = '<div id="modal-[a-zA-Z0-9]+" class="modal fade event_[a-zA-Z0-9_]+".*>.*</div>.*';
//        $pattern .= '<script>.*</script>.*';
//        $pattern .= '<a .* class="(iconify)? dialog event_[a-zA-Z0-9_]+" .* data-toggle="modal" data-target="#modal-[a-zA-Z0-9]+">.*</a>';
//        $this->assertRegExp("`(<li>.*$pattern.*</li>.*){3}`s", $markup);
    }
    
    public function testCanReplacePatternByTargetAttributeInPageTitle()
    {
        $this->routeMatch->setMatchedRouteName('contact');
        $this->helper->withTarget(new TargetWithId());
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/replace-pattern.phtml'), $markup);
    }
    
    public function testCanRemovePreviouslyAddedParamOnPagesHavingSegmentRoute()
    {
        $this->helper->addParams(array('source' => 'db', 'branch' => 'user'))
                     ->removeParam('branch');
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/add-remove-param.phtml'), $markup);
    }
    
    public function testCanReset()
    {
        $this->helper->except('ajouter-identite')
                     ->exceptRoute('contact/ajouter/adresse')
                     ->reset();
        $markup = $this->helper->render();
        $this->assertEquals($this->getExpected('menu-contextuel/default.phtml'), $markup);
    }
}

class TargetWithoutId 
{
    public function __toString() {
        return "12";
    }
}

class TargetWithId 
{
    public $id = 12;
    public function __toString() {
        return "coucou";
    }
}

/**
 * Ce qui suit permet de redéfinir des fonctions PHP standards utilisées par la classe testée
 * afin de faciliter les tests.
 * NB: le namespace doit être le même que celui de la classe testée.
 */
namespace UnicaenApp\View\Helper\Navigation;

function md5($str)
{
    return 'md5result';
}