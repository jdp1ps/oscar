<?php
/**
 * Tests Selenium
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class HomepageTest extends PHPUnit_Extensions_Selenium2TestCase
{
    protected $browserUrl = BROWSER_URL; // cf. phpunit.xml

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

    public function testPageTitleExists()
    {
        $this->url($this->browserUrl);
        try {
            $this->byCssSelector($sel = '#navbar a.brand > h1.title');
        } catch (RuntimeException $exc) {
            $this->fail("Titre de page introuvable avec le sélecteur CSS '$sel'.");
        }
    }

    public function testBreadcrumbsExists()
    {
        $this->url($this->browserUrl);
        try {
            $ul  = $this->byCssSelector($sel = 'div.navbar.breadcrumbs ul.breadcrumb');
        } catch (RuntimeException $exc) {
            $this->fail("Titre de page introuvable avec le sélecteur CSS '$sel'.");
        }
        try {
            $this->assertEquals("Accueil", $ul->byCssSelector($sel = 'li')->text());
        } catch (RuntimeException $exc) {
            $this->fail("Aucun <li>  trouvé dans le fil d'Ariane.");
        }
    }

    public function testVerticalMenuExists()
    {
        $this->url($this->browserUrl);
        try {
            $this->byId($id = 'navigation-secondaire');
        } catch (RuntimeException $exc) {
            $this->fail("Menu vertical introuvable avec l'id '$id'.");
        }
    }

    public function testFooterMenuExists()
    {
        $this->url($this->browserUrl);
        try {
            $footerUl = $this->byCssSelector($sel      = '#footer ul.navigation');
        } catch (RuntimeException $exc) {
            $this->fail("Élément <ul> introuvable avec le sélecteur CSS '$sel'.");
        }
        $selectorsAndTexts = array(
            'li a.ucbn'    => "Université de Caen Normandie",
            'li a.apropos' => "À propos",
            'li a.contact' => "Contact",
            'li a.plan'    => "Plan de navigation",
            'li a.ml'      => "Mentions légales",
            'li a.il'      => "Informatique et libertés");
        foreach ($selectorsAndTexts as $selector => $text) {
            try {
                $this->assertEquals($text, $footerUl->byCssSelector($selector)->text());
            } catch (Exception $exc) {
                $this->fail("Élément <li> introuvable avec le sélecteur CSS '$selector'.");
            }
        }
    }

    public function url($url = NULL)
    {
        $res    = parent::url($url);
        $window = $this->currentWindow(); /* @var $window PHPUnit_Extensions_Selenium2TestCase_Window */
        $window->position(array('x' => 0, 'y' => 0));
        $window->size(array('width'  => 1024, 'height' => 768));
        return $res;
    }
}