<?php
namespace UnicaenAuthTest\View\Helper;

use UnicaenAppTest\View\Helper\TestAsset\ArrayTranslatorLoader;
use UnicaenAuth\View\Helper\UserCurrent;
use Zend\I18n\Translator\Translator;

/**
 * Description of UserCurrentTest
 *
 * @property UserCurrent $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserCurrentTest extends AbstractTest
{
    protected $helperClass = 'UnicaenAuth\View\Helper\UserCurrent';
    protected $renderer;
    protected $authService;
    protected $userStatusHelper;
    protected $userProfileHelper;
    protected $userInfoHelper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->userStatusHelper   = $userStatusHelper   = $this->getMock('UnicaenAuth\View\Helper\UserStatus', ['__invoke']);
        $this->userProfileHelper  = $userProfileHelper  = $this->getMock('UnicaenAuth\View\Helper\UserProfile', ['__toString']);
        $this->userInfoHelper     = $userInfoHelper     = $this->getMock('UnicaenAuth\View\Helper\UserInfo', ['__invoke']);
        $this->inlineScriptHelper = $inlineScriptHelper = $this->getMock('Zend\View\Helper\InlineScript', ['__invoke']);

        $this->userStatusHelper
                ->expects($this->any())
                ->method('__invoke')
                ->will($this->returnValue('User Status Helper Markup'));
        $this->userProfileHelper
                ->expects($this->any())
                ->method('__toString')
                ->will($this->returnValue('User Profile Helper Markup'));
        $this->userInfoHelper
                ->expects($this->any())
                ->method('__invoke')
                ->will($this->returnValue('User Info Helper Markup'));
        $this->inlineScriptHelper
                ->expects($this->any())
                ->method('__invoke')
                ->will($this->returnValue('InlineScript Helper Markup'));

        $this->renderer = $this->getMock('Zend\View\Renderer\PhpRenderer', ['plugin']);
        $this->renderer->expects($this->any())
                       ->method('plugin')
                       ->will($this->returnCallback(function ($helper) use ($userStatusHelper, $userProfileHelper, $userInfoHelper, $inlineScriptHelper) {
                           if ('userstatus' === strtolower($helper)) {
                               return $userStatusHelper;
                           }
                           if ('userprofile' === strtolower($helper)) {
                               return $userProfileHelper;
                           }
                           if ('userinfo' === strtolower($helper)) {
                               return $userInfoHelper;
                           }
                           if ('inlinescript' === strtolower($helper)) {
                               return $inlineScriptHelper;
                           }
                           return null;
                       }));

        $this->authService = $this->getMock('Zend\Authentication\AuthenticationService', ['hasIdentity', 'getIdentity']);

        $this->helper->setAuthService($this->authService)
                     ->setView($this->renderer)
                     ->setTranslator(new Translator());
    }

    public function testCanConstructWithAuthService()
    {
        $helper = new UserCurrent($this->authService);
        $this->assertSame($this->authService, $helper->getAuthService());
    }

    public function testEntryPointReturnsSelfInstance()
    {
        $this->assertSame($this->helper, $this->helper->__invoke());
    }

    public function testEntryPointCanSetArgs()
    {
        $this->helper->__invoke($flag = true);
        $this->assertSame($flag, $this->helper->getAffectationFineSiDispo());
    }

    public function testCanRenderIfNoIdentityAvailable()
    {
        $this->authService->expects($this->any())
                          ->method('hasIdentity')
                          ->will($this->returnValue(false));

        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected('user_current/logged-out.phtml'), $markup);

        // traduction
        $this->helper->setTranslator($this->_getTranslator());
        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected('user_current/logged-out-translated.phtml'), $markup);
    }

    public function testCanRenderLogoutLinkIfIdentityAvailable()
    {
        $this->authService->expects($this->any())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->any())
                          ->method('getIdentity')
                          ->will($this->returnValue($identity = 'Auth Service Identity'));

        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected('user_current/logged-in.phtml'), $markup);

        // traduction
        $this->helper->setTranslator($this->_getTranslator());
        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected('user_current/logged-in-translated.phtml'), $markup);
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
            "Utilisateur connecté à l'application" => "Auth user",
            "Aucun"                                => "None",
        ];

        $translator = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);

        return $translator;
    }
}