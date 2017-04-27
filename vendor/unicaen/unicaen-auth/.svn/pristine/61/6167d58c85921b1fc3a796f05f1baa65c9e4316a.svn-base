<?php
namespace UnicaenAuthTest\View\Helper;

use UnicaenAppTest\View\Helper\TestAsset\ArrayTranslatorLoader;
use UnicaenAuth\View\Helper\UserInfo;
use Zend\I18n\Translator\Translator;

/**
 * Description of AppConnectionTest
 *
 * @property UserInfo $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserInfoTest extends AbstractTest
{
    protected $helperClass = 'UnicaenAuth\View\Helper\UserInfo';
    protected $renderer;
    protected $authService;
    protected $mapperStructure;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->authService = $this->getMock('Zend\Authentication\AuthenticationService', ['hasIdentity', 'getIdentity']);

        $this->mapperStructure = $this->getMock('UnicaenApp\Mapper\Ldap\Structure',
                ['findOneByDn', 'findOnePathByCodeStructure', 'findAllPathByCodeStructure', 'findOneByCodeEntite']);

        $this->helper->setMapperStructure($this->mapperStructure)
                     ->setAuthService($this->authService)
                     ->setTranslator(new Translator());
    }

    public function testCanConstructWithAuthService()
    {
        $helper = new UserInfo($this->authService);
        $this->assertSame($this->authService, $helper->getAuthService());
    }

    public function testEntryPointReturnsSelfInstance()
    {
        $this->assertSame($this->helper, $this->helper->__invoke());
    }

    public function testEntryPointCanSetArgs()
    {
        $this->helper->__invoke($flag = true);
        $this->assertSame($flag, $this->helper->getAffectationPrincipale());
    }

    public function testRenderingReturnsEmptyStringIfNoIdentityAvailable()
    {
        $this->authService->expects($this->any())
                          ->method('hasIdentity')
                          ->will($this->returnValue(false));

        $markup = (string) $this->helper;
        $this->assertEquals('', $markup);
    }

    public function testRenderingReturnsInfoMessageIfUnexpectedIdentityAvailable()
    {
        $this->authService->expects($this->any())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->any())
                          ->method('getIdentity')
                          ->will($this->returnValue($identity = 'Auth Service Identity'));

        $markup = (string) $this->helper;
        $this->assertEquals("Aucune information disponible.", $markup);

        // traduction
        $this->helper->setTranslator($this->_getTranslator());
        $markup = (string) $this->helper;
        $this->assertEquals("No info available.", $markup);
    }

    public function getInfosAndExpectedScript()
    {
        return [
            'rien' => [
                [],
                [],
                [],
                'user_info/rien.phtml',
                'user_info/rien-translated.phtml',
            ],
            'aff-admin-seule' => [
                ["Chemin > Structure > Affectation"],
                [],
                [],
                'user_info/aff-admin-seule.phtml',
                'user_info/aff-admin-seule-translated.phtml',
            ],
            'aff-rech-seule' => [
                [],
                ["Chemin > Structure > Recherche"],
                [],
                'user_info/aff-rech-seule.phtml',
                'user_info/aff-rech-seule-translated.phtml',
            ],
            'fonct-seule' => [
                [],
                [],
                ["Responsable (DSI)"],
                'user_info/fonct-seule.phtml',
                'user_info/fonct-seule-translated.phtml',
            ],
            'pas-fonct' => [
                ["Chemin > Structure > Affectation"],
                ["Chemin > Structure > Recherche"],
                [],
                'user_info/pas-fonct.phtml',
                'user_info/pas-fonct-translated.phtml',
            ],
            'pas-aff-admin' => [
                [],
                ["Chemin > Structure > Recherche"],
                ["Responsable (DSI)"],
                'user_info/pas-aff-admin.phtml',
                'user_info/pas-aff-admin-translated.phtml',
            ],
            'pas-aff-rech' => [
                ["Chemin > Structure > Affectation"],
                [],
                ["Responsable (DSI)"],
                'user_info/pas-aff-rech.phtml',
                'user_info/pas-aff-rech-translated.phtml',
            ],
            'tout' => [
                ["Chemin > Structure > Affectation"],
                ["Chemin > Structure > Recherche"],
                ["Responsable (DSI)"],
                'user_info/tout.phtml',
                'user_info/tout-translated.phtml',
            ],
        ];
    }

    /**
     * @dataProvider getInfosAndExpectedScript
     * @param array $affectationsAdmin
     * @param array $affectationsRecherche
     * @param array $fonctionsStructurelles
     * @param string $expectedScript
     * @param string $expectedScriptTranslated
     */
    public function testCanRenderLogoutLinkIfIdentityAvailable(
            $affectationsAdmin,
            $affectationsRecherche,
            $fonctionsStructurelles,
            $expectedScript,
            $expectedScriptTranslated)
    {
        $identity = $this->getMock(
                'UnicaenApp\Entity\Ldap\People',
                ['getAffectationsAdmin', 'getAffectationsRecherche', 'getFonctionsStructurelles'],
                [],
                '',
                false);
        $identity->expects($this->any())
                 ->method('getAffectationsAdmin')
                 ->will($this->returnValue($affectationsAdmin));
        $identity->expects($this->any())
                 ->method('getAffectationsRecherche')
                 ->will($this->returnValue($affectationsRecherche));
        $identity->expects($this->any())
                 ->method('getFonctionsStructurelles')
                 ->will($this->returnValue($fonctionsStructurelles));

        $this->authService->expects($this->any())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->any())
                          ->method('getIdentity')
                          ->will($this->returnValue($identity));

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
            "Aucune information disponible." => "No info available.",
            "Affectations administratives"   => "Administrative affectations",
            "Affectations recherche"         => "Research affectations",
            "Responsabilités"                => "Responsabilities",
            "Aucune affectation trouvée."    => "No affectation found.",
        ];

        $translator = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);

        return $translator;
    }
}