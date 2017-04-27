<?php
namespace UnicaenAppTest\View\Helper;

use stdClass;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\View\Helper\AppInfos;
use Zend\Config\Config;
use Zend\I18n\Translator\Translator;

/**
 * Description of AppInfosTest
 *
 * @property AppInfos $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AppInfosTest extends AbstractTest
{
    protected $helperClass = 'UnicaenApp\View\Helper\AppInfos';
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->helper->setTranslator(new Translator());
    }
    
    public function testReturnsSelfWhenInvoked()
    {
        $this->assertSame($this->helper, $this->helper->__invoke());
    }
    
    public function testCanGetDefaultConfig()
    {
        $this->assertInstanceOf('Zend\Config\Config', $config = $this->helper->getConfig());
        $this->assertEmpty($config->toArray());
    }
    
    public function getConfig()
    {
        $array = array(
            'nom'              => "Nom de l'application",
            'version'          => "1.3.0",
            'not-allowed-prop' => 'foo'
        );
        return array(
            array($array),
            array(new Config($array)),
        );
    }
    
    /**
     * @dataProvider getConfig
     */
    public function testCanSetConfig($config)
    {
        $this->helper->setConfig($config);
        $this->assertInstanceOf('Zend\Config\Config', $this->helper->getConfig());
    }
    
    /**
     * @dataProvider getConfig
     */
    public function testConstructorAcceptsConfig($config)
    {
        $helper = new AppInfos($config);
        $this->assertInstanceOf('Zend\Config\Config', $helper->getConfig());
    }
    
    /**
     * @dataProvider getConfig
     */
    public function testCanAccessConfigAttribute($config)
    {
        $helper = new AppInfos($config);
        $this->assertInstanceOf('Zend\Config\Config', $config = $helper->getConfig());
        $this->assertEquals("Nom de l'application", $helper->nom);
        $this->assertEquals("1.3.0", $helper->version);
    }
    
    /**
     * @dataProvider getConfig
     */
    public function testFiltersConfig($config)
    {
        $helper = new AppInfos($config);
        $this->assertInstanceOf('\Zend\Config\Config', $config = $helper->getConfig());
        $this->assertNull($config->get('foo'));
    }
    
    public function getInvalidConfig()
    {
        return array(
            array(null),
            array("Hello"),
            array(12),
            array(new stdClass()),
        );
    }
    
    /**
     * @dataProvider getInvalidConfig
     * @expectedException LogicException
     * @expectedExceptionMessage invalide
     */
    public function testThrowsExceptionWhenSettingInvalidConfig($config)
    {
        $this->helper->setConfig($config);
    }
    
    public function getEmptyConfig()
    {
        return array(
            array(array()),
            array(new Config(array())),
        );
    }
    
    /**
     * @dataProvider getEmptyConfig
     * @expectedException LogicException
     * @expectedExceptionMessage vide
     */
    public function testThrowsExceptionWhenSettingEmptyConfig($config)
    {
        $this->helper->setConfig($config);
    }
    
    public function getConfigAndExpectedScript()
    {
        return array(
            /**
             * format HTML
             */
            array(
                'html-contact-aucun' => array(
                    'nom'     => "Mon application",
                    'desc'    => "Magnifique appli!",
                    'version' => '1.3.0',
                    'date'    => '01/07/2013',
                ),
                'htmlListFormat' => true,
                'includeContact' => true,
                'script' => 'app-infos/html/contact-aucun.phtml',
            ),
            array(
                'html-contact-vide' => array(
                    'nom'     => "Mon application",
                    'desc'    => "Magnifique appli!",
                    'version' => '1.3.0',
                    'date'    => '01/07/2013',
                    'contact' => null,
                ),
                'htmlListFormat' => true,
                'includeContact' => true,
                'script' => 'app-infos/html/contact-aucun.phtml',
            ),
            array(
                'html-contact-exclu' => array(
                    'nom'     => "Mon application",
                    'desc'    => "Magnifique appli!",
                    'version' => '1.3.0',
                    'date'    => '01/07/2013',
                    'contact' => 'e.mail@domain.fr',
                ),
                'htmlListFormat' => true,
                'includeContact' => false,
                'script' => 'app-infos/html/contact-exclu.phtml',
            ),
            array(
                'html-contact-simple' => array(
                    'nom'     => "Mon application",
                    'desc'    => "Magnifique appli!",
                    'version' => '1.3.0',
                    'date'    => '01/07/2013',
                    'contact' => 'e.mail@domain.fr',
                ),
                'htmlListFormat' => true,
                'includeContact' => true,
                'script' => 'app-infos/html/contact-simple.phtml',
            ),
            array(
                'html-contact-multi-array' => array(
                    'nom'     => "Mon application",
                    'desc'    => "Magnifique appli!",
                    'version' => '1.3.0',
                    'date'    => '01/07/2013',
                    'contact' => array('e.mail@domain.fr', '01 02 03 04 05'),
                ),
                'htmlListFormat' => true,
                'includeContact' => true,
                'script' => 'app-infos/html/contact-multi.phtml',
            ),
            array(
                'html-contact-multi-config' => array(
                    'nom'     => "Mon application",
                    'desc'    => "Magnifique appli!",
                    'version' => '1.3.0',
                    'date'    => '01/07/2013',
                    'contact' => new Config(array('e.mail@domain.fr', '01 02 03 04 05')),
                ),
                'htmlListFormat' => true,
                'includeContact' => true,
                'script' => 'app-infos/html/contact-multi.phtml',
            ),
            
            /**
             * format texte
             */
            array(
                'text-contact-aucun' => array(
                    'nom'     => "Mon application",
                    'desc'    => "Magnifique appli!",
                    'version' => '1.3.0',
                    'date'    => '01/07/2013',
                ),
                'htmlListFormat' => false,
                'includeContact' => true,
                'script' => 'app-infos/text/contact-aucun.phtml',
            ),
            array(
                'text-contact-vide' => array(
                    'nom'     => "Mon application",
                    'desc'    => "Magnifique appli!",
                    'version' => '1.3.0',
                    'date'    => '01/07/2013',
                    'contact' => null,
                ),
                'htmlListFormat' => false,
                'includeContact' => true,
                'script' => 'app-infos/text/contact-aucun.phtml',
            ),
            array(
                'text-contact-exclu' => array(
                    'nom'     => "Mon application",
                    'desc'    => "Magnifique appli!",
                    'version' => '1.3.0',
                    'date'    => '01/07/2013',
                    'contact' => 'e.mail@domain.fr',
                ),
                'htmlListFormat' => false,
                'includeContact' => false,
                'script' => 'app-infos/text/contact-exclu.phtml',
            ),
            array(
                'text-contact-simple' => array(
                    'nom'     => "Mon application",
                    'desc'    => "Magnifique appli!",
                    'version' => '1.3.0',
                    'date'    => '01/07/2013',
                    'contact' => 'e.mail@domain.fr',
                ),
                'htmlListFormat' => false,
                'includeContact' => true,
                'script' => 'app-infos/text/contact-simple.phtml',
            ),
            array(
                'text-contact-multi-array' => array(
                    'nom'     => "Mon application",
                    'desc'    => "Magnifique appli!",
                    'version' => '1.3.0',
                    'date'    => '01/07/2013',
                    'contact' => array('e.mail@domain.fr', '01 02 03 04 05'),
                ),
                'htmlListFormat' => false,
                'includeContact' => true,
                'script' => 'app-infos/text/contact-multi.phtml',
            ),
            array(
                'text-contact-multi-config' => array(
                    'nom'     => "Mon application",
                    'desc'    => "Magnifique appli!",
                    'version' => '1.3.0',
                    'date'    => '01/07/2013',
                    'contact' => new Config(array('e.mail@domain.fr', '01 02 03 04 05')),
                ),
                'htmlListFormat' => false,
                'includeContact' => true,
                'script' => 'app-infos/text/contact-multi.phtml',
            ),
        );
    }
    
    /**
     * @dataProvider getConfigAndExpectedScript
     * @param array $config
     * @param boolean $htmlListFormat
     * @param boolean $includeContact
     * @param string $expectedScript
     */
    public function testCanGenerateCorrectMarkup($config, $htmlListFormat, $includeContact, $expectedScript)
    {
        $this->helper->setConfig($config)
                     ->setHtmlListFormat($htmlListFormat)
                     ->setIncludeContact($includeContact);
        $markup = "" . $this->helper;
        $this->assertEquals($this->getExpected($expectedScript), $markup);
    }
}