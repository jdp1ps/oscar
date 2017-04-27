<?php
namespace UnicaenAuthTest\View\Helper;

use UnicaenAppTest\View\Helper\TestAsset\ArrayTranslatorLoader;
use UnicaenAuth\View\Helper\UserConnection;
use Zend\I18n\Translator\Translator;

/**
 * Description of AppConnectionTest
 *
 * @property UserConnection $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserConnectionTest extends AbstractTest
{
    protected $helperClass = 'UnicaenAuth\View\Helper\UserConnection';
    protected $renderer;
    protected $authService;
    protected $urlHelper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->urlHelper = $this->getMock('Zend\View\Helper\Url', ['__invoke']);

        $this->renderer = $this->getMock('Zend\View\Renderer\PhpRenderer', ['plugin']);
        $this->renderer->expects($this->any())
                       ->method('plugin')
                       ->with('url')
                       ->will($this->returnValue($this->urlHelper));

        $this->authService = $this->getMock('Zend\Authentication\AuthenticationService', ['hasIdentity', 'getIdentity']);

        $this->helper->setAuthService($this->authService)
                     ->setView($this->renderer)
                     ->setTranslator(new Translator());
    }

    public function testCanConstructWithAuthService()
    {
        $helper = new UserConnection($this->authService);
        $this->assertSame($this->authService, $helper->getAuthService());
    }

    public function testEntryPointReturnsSelfInstance()
    {
        $this->assertSame($this->helper, $this->helper->__invoke());
    }

    public function testCanRenderLoginLinkIfNoIdentityAvailable()
    {
        $this->authService->expects($this->any())
                          ->method('hasIdentity')
                          ->will($this->returnValue(false));

        $this->urlHelper
                ->expects($this->any())
                ->method('__invoke')
                ->with('zfcuser/login')
                ->will($this->returnValue('/appli/connexion'));

        $this->assertEquals($this->getExpected('user_connection/login.phtml'), (string) $this->helper);

        // traduction
        $this->helper->setTranslator($this->_getTranslator());
        $this->assertEquals($this->getExpected('user_connection/login-translated.phtml'), (string) $this->helper);
    }

    public function testCanRenderLogoutLinkIfIdentityAvailable()
    {
        $this->authService->expects($this->any())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->any())
                          ->method('getIdentity')
                          ->will($this->returnValue($identity = 'Auth Service Identity'));

        $this->urlHelper
                ->expects($this->any())
                ->method('__invoke')
                ->with('zfcuser/logout')
                ->will($this->returnValue('/appli/deconnexion'));

        $this->assertEquals($this->getExpected('user_connection/logout.phtml'), (string) $this->helper);

        // traduction
        $this->helper->setTranslator($this->_getTranslator());
        $this->assertEquals($this->getExpected('user_connection/logout-translated.phtml'), (string) $this->helper);
    }

    /**
     * Returns translator
     *
     * @return Translator
     */
    protected function _getTranslator()
    {
        $loader = new ArrayTranslatorLoader();
        $loader->translations = [
            "Connexion"                                => 'Login',
            "Déconnexion"                              => 'Logout',
            "Affiche le formulaire d'authentification" => 'Display auth form',
            "Supprime les informations de connexion"   => 'Reset auth info',
        ];

        $translator = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);

        return $translator;
    }
}