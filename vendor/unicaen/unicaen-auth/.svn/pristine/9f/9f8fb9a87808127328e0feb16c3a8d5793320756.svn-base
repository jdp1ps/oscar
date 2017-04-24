<?php
namespace UnicaenAuthTest\View\Helper;

use UnicaenApp\Entity\Ldap\People as LdapPeopleEntity;
use UnicaenAppTest\Entity\Ldap\TestAsset\People as LdapPeopleTestAsset;
use UnicaenAppTest\View\Helper\TestAsset\ArrayTranslatorLoader;
use UnicaenAuth\View\Helper\UserStatus;
use Zend\I18n\Translator\Translator;

/**
 * Description of UserProfileTest
 *
 * @property UserStatus $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserStatusTest extends AbstractTest
{
    protected $helperClass = 'UnicaenAuth\View\Helper\UserStatus';

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->authService = $this->getMock('Zend\Authentication\AuthenticationService', ['hasIdentity', 'getIdentity']);

        $userConnectionHelper = $this->getMock('UnicaenAuth\View\Helper\UserConnection', ['__toString']);
        $userConnectionHelper->expects($this->any())
                             ->method('__toString')
                             ->will($this->returnValue('UserConnection Helper Markup'));

        $this->helper->getView()->getHelperPluginManager()->setService('userConnection', $userConnectionHelper);

        $this->helper->setDisplayConnectionLink()
                     ->setAuthService($this->authService);
    }

    public function testEntryPointReturnsSelfInstance()
    {
        $this->assertSame($this->helper, $this->helper->__invoke());
    }

    public function testEntryPointCanSetArgs()
    {
        $this->helper->__invoke($flag = true);
        $this->assertSame($flag, $this->helper->getDisplayConnectionLink());
    }

    public function testRenderingWithoutConnectionLinkReturnsNoneIfNoIdentityAvailable()
    {
        $this->authService->expects($this->any())
                          ->method('hasIdentity')
                          ->will($this->returnValue(false));

        $this->helper->setDisplayConnectionLink(false);

        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected('user_status/no-identity-without-link.phtml'), $markup);

        // traduction
        $this->helper->setTranslator($this->_getTranslator());
        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected('user_status/no-identity-without-link-translated.phtml'), $markup);
    }

    public function getIdentityAndExpectedScript()
    {
        return [
            'identity-to-string' => [
                new IdentityTestAsset1(),
                'user_status/identity-without-link.phtml',
                'user_status/identity-with-link.phtml',
            ],
            'identity-get-displayname' => [
                new IdentityTestAsset2(),
                'user_status/identity-without-link.phtml',
                'user_status/identity-with-link.phtml',
            ],
            'identity-get-username' => [
                new IdentityTestAsset3(),
                'user_status/identity-without-link.phtml',
                'user_status/identity-with-link.phtml',
            ],
            'identity-get-id' => [
                new IdentityTestAsset4(),
                'user_status/identity-without-link.phtml',
                'user_status/identity-with-link.phtml',
            ],
            'unexpected-identity' => [
                new \DateTime(),
                'user_status/unexpected-identity-without-link.phtml',
                'user_status/unexpected-identity-with-link.phtml',
            ],
        ];
    }

    /**
     * @dataProvider getIdentityAndExpectedScript
     * @param mixed $identity
     * @param string $expectedScriptWithoutLink
     */
    public function testRenderingWithoutConnectLinkReturnsCorrectMarkupIfIdentityAvailable(
            $identity,
            $expectedScriptWithoutLink)
    {
        $this->authService->expects($this->any())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->any())
                          ->method('getIdentity')
                          ->will($this->returnValue($identity));

        $this->helper->setDisplayConnectionLink(false);

        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected($expectedScriptWithoutLink), $markup);
    }

    public function testRenderingWithConnectionLinkReturnsNoneIfNoIdentityAvailable()
    {
        $this->authService->expects($this->any())
                          ->method('hasIdentity')
                          ->will($this->returnValue(false));

        $this->helper->setDisplayConnectionLink(true);

        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected('user_status/no-identity-with-link.phtml'), $markup);

        // traduction
        $this->helper->setTranslator($this->_getTranslator());
        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected('user_status/no-identity-with-link-translated.phtml'), $markup);
    }

    /**
     * @dataProvider getIdentityAndExpectedScript
     * @param mixed $identity
     * @param string $expectedScriptWithoutLink
     * @param string $expectedScriptWithLink
     */
    public function testRenderingWithConnectLinkReturnsCorrectMarkupIfIdentityAvailable(
            $identity,
            $expectedScriptWithoutLink,
            $expectedScriptWithLink)
    {
        $this->authService->expects($this->any())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->any())
                          ->method('getIdentity')
                          ->will($this->returnValue($identity));

        $this->helper->setDisplayConnectionLink(true);

        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected($expectedScriptWithLink), $markup);
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
            "Vous n'êtes pas connecté(e)" => "None",
        ];

        $translator = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);

        return $translator;
    }
}

class IdentityTestAsset1
{
    public function __toString()
    {
        return 'User identity';
    }
}

class IdentityTestAsset2
{
    public function getDisplayName()
    {
        return 'User identity';
    }
}

class IdentityTestAsset3
{
    public function getUsername()
    {
        return 'User identity';
    }
}

class IdentityTestAsset4
{
    public function getId()
    {
        return 'User identity';
    }
}