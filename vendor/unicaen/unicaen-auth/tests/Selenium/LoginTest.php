<?php
/**
 * Test Selenium
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class LoginTest extends PHPUnit_Extensions_Selenium2TestCase
{
    protected $browserUrl                 = BROWSER_URL; // cf. phpunit.xml
    protected $casLoginUrl                = CAS_URL;
    protected $username                   = USERNAME;
    protected $password                   = PASSWORD;
    protected $appLoginUrl                = '/auth/connexion';
    protected $logoutUrl                  = '/auth/deconnexion';
    protected $loginLinkText              = "Connexion";
    protected $logoutLinkText             = "Déconnexion";
    protected $noUserConnectedInfoLinkText = "Aucun";

    public static function setUpBeforeClass()
    {
        static::shareSession(true); // plus rapide, et indispensable pour nourrir un test par un autre avec @depends
    }

    protected function setUp()
    {
        $this->setHost(HOST);         // cf. phpunit.xml
        $this->setPort(intval(PORT)); // cf. phpunit.xml
        $this->setBrowser('firefox');
        $this->setBrowserUrl($this->browserUrl);
    }

    public function testLogoutRedirectsToHomePage()
    {
        $this->url($this->logoutUrl);
        $this->assertEquals($this->browserUrl, $this->url());
    }

    public function testLoginRequestReturnsLoginPage()
    {
        $this->url($this->appLoginUrl);
        $this->assertTrue(
                $this->isLoginPage('app') || $this->isLoginPage('cas'), "L'URL '$this->appLoginUrl' ne conduit ni à la page d'authentification CAS ni à celle de l'application.");
    }

    /**
     *
     * @param int $width
     * @param bool $visible
     * @depends testLogoutRedirectsToHomePage
     * @dataProvider getWindowWidthAndCorrespondingLoginLinkVisibility
     */
    public function testLoginLinkVisibilityDependsOnWindowWidth($width, $visible)
    {
        $this->url($this->logoutUrl);
        $window = $this->currentWindow(); /* @var $window PHPUnit_Extensions_Selenium2TestCase_Window */
        $window->position(['x' => 0, 'y' => 0]);
        $window->size(['width'  => $width, 'height' => 600]);
        if ($visible) {
            try {
                $this->assertTrue($this->byLinkText($this->loginLinkText)->displayed());
            } catch (RuntimeException $exc) {
                $this->fail("Le lien de connexion devrait être visible avec une largeur de fenêtre de $width.");
            }
        }
        else {
            try {
                $this->byLinkText($this->loginLinkText);
                $this->fail("Le lien de connexion ne devrait pas être visible avec une largeur de fenêtre de $width.");
            } catch (RuntimeException $exc) {

            }
        }
    }

    public function getWindowWidthAndCorrespondingLoginLinkVisibility()
    {
        return [
            // width,   visibility
            [1600, true],
            [1200, true],
            [1024, true],
            [800, false],
        ];
    }

    public function testClickOnLoginLinkSendsToLoginPage()
    {
        $this->url($this->logoutUrl);
        $link = $this->byLinkText($this->loginLinkText);
        $link->click();

//        // 2 cas de figure :
//        // - soit l'auth CAS est activée et alors il y a une redirection vers la page d'auth CAS ;
//        // - soit elle est désactivée et alors il y a une redirection vers la page d'auth de l'appli.
        $this->assertTrue(
                $this->isLoginPage('app') || $this->isLoginPage('cas'), "Le clic sur le lien Connexion ne conduit ni à la page d'authentification CAS ni à celle de l'application.");
    }

    /**
     * @depends testLogoutRedirectsToHomePage
     * @depends testLoginRequestReturnsLoginPage
     */
    public function testEnterValidCredentialOnLoginPageRedirectsToHomePageAndConnectsUser()
    {
        $this->url($this->logoutUrl);   // déconnexion assurée
        $this->url($this->appLoginUrl);
        if ($this->isLoginPage('app')) {
            $usernameElement = 'identity';
            $passwordElement = 'credential';
        }
        elseif ($this->isLoginPage('cas')) {
            $usernameElement = 'username';
            $passwordElement = 'password';
        }
        $usernameInput = $this->byName($usernameElement);
        $usernameInput->value(USERNAME);
        $usernameInput = $this->byName($passwordElement);
        $usernameInput->value(PASSWORD);
        try {
            $this->byCssSelector('form')->submit();
        } catch (RuntimeException $exc) {
            $this->fail("Formulaire de connexion introuvable.");
        }
        $this->assertEquals($this->browserUrl, $this->url());
//        file_put_contents('/tmp/screenshot.png', $this->currentScreenshot());
        try {
            $userConnectionInfoLink = $this->byId($id = 'user-current-info');
        } catch (RuntimeException $exc) {
            $this->fail("Lien introuvable avec l'id '$id'.");
        }
        $this->assertNotEquals($this->noUserConnectedInfoLinkText, $userConnectionInfoLink->text());
        try {
            $logoutLink = $this->byLinkText($this->logoutLinkText);
        } catch (RuntimeException $exc) {
            $this->fail("Lien de déconnexion introuvable avec le libellé '$this->logoutLinkText'.");
        }
        return $logoutLink;
    }

    /**
     *
     * @param PHPUnit_Extensions_Selenium2TestCase_Element $logoutLink
     * @depends testEnterValidCredentialOnLoginPageRedirectsToHomePageAndConnectsUser
     */
    public function testClickOnLogoutLinkRedirectsToHomePageAndDisconnectsUser(PHPUnit_Extensions_Selenium2TestCase_Element $logoutLink)
    {
        $logoutLink->click();
        $this->assertEquals($this->browserUrl, $this->url());
        try {
            $this->byLinkText($this->loginLinkText);
        } catch (RuntimeException $exc) {
            $this->fail("Lien de connexion introuvable avec le libellé '$this->loginLinkText'.");
        }
        try {
            $userConnectionInfoLink = $this->byId($id = 'user-current-info');
        } catch (RuntimeException $exc) {
            $this->fail("Lien introuvable avec l'id '$id'.");
        }
        $this->assertEquals($this->noUserConnectedInfoLinkText, $userConnectionInfoLink->text());
    }

    /**
     *
     */
    public function testFooterMenuElements()
    {
        $this->url($this->browserUrl);
        try {
            $footerUl = $this->byCssSelector($sel = '#footer ul.navigation');
        } catch (RuntimeException $exc) {
            $this->fail("Élément <ul> introuvable avec le sélecteur CSS '$sel'.");
        }
        $selectorsAndTexts = [
            'li a.ucbn'    => "Université de Caen - Basse Normandie",
            'li a.apropos' => "À propos",
            'li a.contact' => "Contact",
            'li a.plan'    => "Plan de navigation",
            'li a.ml'      => "Mentions légales",
            'li a.il'      => "Informatique et libertés"];
        foreach ($selectorsAndTexts as $selector => $text) {
            try {
                $this->assertEquals($text, $footerUl->byCssSelector($selector)->text());
            } catch (Exception $exc) {
                $this->fail("Élément <li> introuvable avec le sélecteur CSS '$selector'.");
            }
        }
    }

    /**
     * Retourne <code>true</code> si la page courante est la page de connexion,
     * éventuellement du type spécifié.
     *
     * @param bool $type null, 'app' ou 'cas'
     * @return bool
     */
    protected function isLoginPage($type = null)
    {
        $is['app'] = intval(substr($this->url(), 0 - strlen($this->appLoginUrl)) == $this->appLoginUrl);
        $is['cas'] = intval(substr($this->url(), 0 - strlen($this->casLoginUrl)) == $this->casLoginUrl);
        switch ($type) {
            case 'app':
                return (bool) $is['app'];
                break;
            case 'cas':
                return (bool) $is['cas'];
                break;
            case null:
                break;
            default:
                return false;
                break;
        }
        return (bool) array_product($is);
    }

    public function url($url = NULL)
    {
        $res    = parent::url($url);
        $window = $this->currentWindow(); /* @var $window PHPUnit_Extensions_Selenium2TestCase_Window */
        $window->position(['x' => 0, 'y' => 0]);
        $window->size(['width'  => 1024, 'height' => 768]);
        return $res;
    }

}