<?php
namespace UnicaenAuthTest\View\Helper;

use UnicaenAppTest\View\Helper\TestAsset\ArrayTranslatorLoader;
use UnicaenAuth\View\Helper\UserProfile;
use Zend\I18n\Translator\Translator;

/**
 * Description of UserProfileTest
 *
 * @property UserProfile $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserProfileTest extends AbstractTest
{
    protected $helperClass = 'UnicaenAuth\View\Helper\UserProfile';
    protected $identityProvider;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->identityProvider = $this->getMockForAbstractClass('BjyAuthorize\Provider\Identity\ProviderInterface', ['getIdentityRoles']);

        $this->helper->setIdentityProvider($this->identityProvider)
                     ->setTranslator(new Translator());
    }

    public function testEntryPointReturnsSelfInstance()
    {
        $this->assertSame($this->helper, $this->helper->__invoke());
    }

    public function getIdentityRolesAndExpectedScript()
    {
        return [
            'none' => [
                [],
                'user_profile/none.phtml',
                'user_profile/none-translated.phtml',
            ],
            'role' => [
                [new \Zend\Permissions\Acl\Role\GenericRole('Invité')],
                'user_profile/role.phtml',
                'user_profile/role-translated.phtml',
            ],
            'named-role' => [
                [new \UnicaenAuth\Acl\NamedRole('admin', null, "Administrateur")],
                'user_profile/named-role.phtml',
                'user_profile/named-role-translated.phtml',
            ],
            'stringable-role' => [
                ['Opérateur'], // (string) 'Opérateur' renverra 'Opérateur'
                'user_profile/stringable-role.phtml',
                'user_profile/stringable-role-translated.phtml',
            ],
            'non-stringable-role' => [
                [new \stdClass()], // (string) new \stdClass() lèvera l'erreur "Object of class stdClass could not be converted to string"
                'user_profile/non-stringable-role.phtml',
                'user_profile/non-stringable-role-translated.phtml',
            ],
        ];
    }

    /**
     * @dataProvider getIdentityRolesAndExpectedScript
     * @param array $roles
     * @param string $expectedScript
     * @param string $expectedScriptTranslated
     */
    public function testRenderingReturnsNoneIfIdentityProviderProvidesNoRole($roles, $expectedScript, $expectedScriptTranslated)
    {
        $this->identityProvider
                ->expects($this->any())
                ->method('getIdentityRoles')
                ->will($this->returnValue($roles));

        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected($expectedScript), $markup);

        // traduction
        $this->helper->setTranslator($this->_getTranslator());
        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected($expectedScriptTranslated), $markup);
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
            "Profil utilisateur" => "User profile",
            "Inconnu"            => "Unknown",
            "Aucun"              => "None",
            "Invité"             => "Guest",
            "Administrateur"     => "Administrator",
            "Opérateur"          => "Operator"
        ];

        $translator = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);

        return $translator;
    }
}