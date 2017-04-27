<?php
namespace UnicaenAppTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Teste que les pages communes à toutes nos applis sont bien servies 
 * par le contrôleur ApplicationController du module UnicaenApp :
 *  - "À propos"
 *  - "Contact"
 *  - "Plan de navigation"
 *  - "Mentions légales"
 *  - "Informatique et libertés"
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see \UnicaenApp\Controller\ApplicationController
 */
class ApplicationControllerTest extends AbstractHttpControllerTestCase
{
    const MODULE_NAME      = 'UnicaenApp';
    const CONTROLLER_NAME  = 'UnicaenApp\Controller\Application';
    const CONTROLLER_CLASS = 'ApplicationController';

    protected $traceError = true; // marche pas ?!

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../config/application.config.php');
        parent::setUp();
    }

    protected function assertCommonStuffIsCorrect()
    {
        $this->assertResponseStatusCode(200);
        $this->assertModuleName(self::MODULE_NAME);
        $this->assertControllerName(self::CONTROLLER_NAME);
        $this->assertControllerClass(self::CONTROLLER_CLASS);
        $this->assertNull($this->getResult());
    }
    
    public function testActionAPropos()
    {
        $this->dispatch('/apropos');
        $this->assertCommonStuffIsCorrect();
        $this->assertMatchedRouteName('apropos');
    }

    public function testActionContact()
    {
        $this->dispatch('/contact');
        $this->assertCommonStuffIsCorrect();
        $this->assertMatchedRouteName('contact');
    }

    public function testActionPlan()
    {
        $this->dispatch('/plan');
        $this->assertCommonStuffIsCorrect();
        $this->assertMatchedRouteName('plan');
    }

    public function testActionMentionsLegales()
    {
        $this->dispatch('/mentions-legales');
        $this->assertCommonStuffIsCorrect();
        $this->assertMatchedRouteName('mentions-legales');
    }

    public function testActionInformatiqueEtLibertes()
    {
        $this->dispatch('/informatique-et-libertes');
        $this->assertCommonStuffIsCorrect();
        $this->assertMatchedRouteName('il');
    }
}