<?php
namespace UnicaenAppTest\Exporter;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Exporter\Pdf;
use mPDF;

/**
 * Tests de la classe d'exportation au format PDF, utilisant la bibliothèque mPDF.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see Pdf
 */
class PdfTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var int
     */
    protected $memoryLimit;
    
    /**
     * @var mPDF
     */
    protected $mPdf;
    
    /**
     * @var Pdf
     */
    protected $exporter;

    /**
     * @var string
     */
    protected $tempDirectoryPath;
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->memoryLimit = ini_get('memory_limit');
        $this->tempDirectoryPath = sys_get_temp_dir();
        
        $this->mPdf = $this->getMock('mPDF', array('Output', 'SetProtection', 'SetWatermarkText'));
        $this->mPdf->expects($this->any())
                   ->method('Output')
                   ->will($this->returnValue('PDF content'));
        
        $this->exporter = new Pdf();
        $this->exporter->setMpdf($this->mPdf);
    }
    
    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
//        ini_set('memory_limit', $this->memoryLimit);
    }
    
    public function testGettingRendererAfterDefaultConstructorReturnsPhpRenderer()
    {
        $exporter = new Pdf();
        $this->assertInstanceOf('\Zend\View\Renderer\PhpRenderer', $exporter->getRenderer());
    }
    
    public function testCanSetRenderer()
    {
        $renderer = new \Zend\View\Renderer\PhpRenderer();
        $exporter = new Pdf($renderer);
        $this->assertSame($renderer, $exporter->getRenderer());
    }
    
    public function testCanGetDefaultMpdfObject()
    {
        $exporter = new Pdf();
        $this->assertInstanceOf('mPDF', $exporter->getMpdf());
    }
    
    public function testCanGetDefaultExportDirectoryPath()
    {
        $this->assertNotEmpty($this->exporter->getExportDirectoryPath());
    }
    
    public function testTmpDirIsSetAndValidWhenGettingMpdfObject()
    {
        $this->exporter->getMpdf();
        if (!defined("_MPDF_TEMP_PATH")) {
            $this->fail("Le répertoire temporaire à utiliser n'a pas été fourni à la bibliothèque mPDF.");
        }
        $this->assertFileExists(_MPDF_TEMP_PATH);
        $this->assertTrue(is_writable(_MPDF_TEMP_PATH));
    }
    
    public function testCanSetPermissions()
    {
        $permissions  = array('copy','print','modify','annot-forms','fill-forms','extract','assemble','print-highres');
        $userPassword = 'User-password-999';
        
        $this->mPdf->expects($this->once())
                   ->method('SetProtection')
                   ->with($permissions, $userPassword);
                
        $this->exporter->setPermissions($permissions, $userPassword);
    }
    
    public function testCanSetWatermark()
    {
        $text  = 'Filigrane';
        
        $this->mPdf->expects($this->once())
                   ->method('SetWatermarkText')
                   ->with($text);
                
        $this->exporter->setWatermark($text);
        
        $this->assertTrue($this->exporter->getMpdf()->showWatermarkText);
    }
    
    public function testCanSetFormat()
    {
        $this->exporter->setFormat('A3');
    }
    
    public function testCanSetOrientationPaysage()
    {
        $this->exporter->setOrientationPaysage();
    }
    
    public function testCanSetMargins()
    {
        $this->exporter->setMarginLeft(20)
                       ->setMarginRight(20)
                       ->setMarginBottom(20)
                       ->setMarginTop(20)
                       ->setMarginHeader(20)
                       ->setMarginFooter(20);
    }
    
    public function testCanSetTitles()
    {
        $this->exporter->setHeaderTitle('Header title')
                       ->setHeaderSubTitle('Header sub-title')
                       ->setFooterTitle('Footer title');
    }
    
    public function getDefaultScriptFileNames()
    {
        return array(
            array('footer-even.phtml'),
            array('footer-odd.phtml'),
            array('header-even.phtml'),
            array('header-odd.phtml'),
        );
    }
    
    /**
     * @dataProvider getDefaultScriptFileNames
     */
    public function testDefaultScriptsPathContainsDefaultScriptFile($fileName)
    {
        $defaultScriptsPath = $this->exporter->getDefaultScriptsPath();
        $this->assertFileExists($defaultScriptsPath . '/' . $fileName);
    }
    
    public function testSettingSameHeaderScriptForBothOddAndEvenPages()
    {
        $this->exporter->setHeaderScript($script = '/path/to/header.phtml');
        $headerScripts = $this->readAttribute($this->exporter, 'headerScripts');
        $this->assertEquals($script, $headerScripts['O']);
        $this->assertEquals($script, $headerScripts['E']);
    }
    
    public function testSettingSameFooterScriptForBothOddAndEvenPages()
    {
        $this->exporter->setFooterScript($script = '/path/to/footer.phtml');
        $footerScripts = $this->readAttribute($this->exporter, 'footerScripts');
        $this->assertEquals($script, $footerScripts['O']);
        $this->assertEquals($script, $footerScripts['E']);
    }
    
    public function getOddAndEvenOptions()
    {
        return array(
            array('O', 'E'),
            array('E', 'O'),
        );
    }
    
    /**
     * @dataProvider getOddAndEvenOptions
     */
    public function testSettingHeaderScriptForOneTypeOfPageDoesNotChangeOtherTypeOfPageScript($oneType, $otherType)
    {
        $this->exporter->setHeaderScript($scriptOneType = '/path/to/header-one-type.phtml', $oneType);
        $headerScripts = $this->readAttribute($this->exporter, 'headerScripts');
        $this->assertEquals($scriptOneType, $headerScripts[$oneType]);
        $this->assertArrayNotHasKey($otherType, $headerScripts);
        
        $this->exporter->setHeaderScript($scriptOtherType = '/path/to/header-other-type.phtml', $otherType);
        $headerScripts = $this->readAttribute($this->exporter, 'headerScripts');
        $this->assertEquals($scriptOtherType, $headerScripts[$otherType]);
        $this->assertEquals($scriptOneType, $headerScripts[$oneType]);
    }
    
    /**
     * @dataProvider getOddAndEvenOptions
     */
    public function testSettingFooterScriptForOneTypeOfPageDoesNotChangeOtherTypeOfPageScript($oneType, $otherType)
    {
        $this->exporter->setFooterScript($scriptOneType = '/path/to/footer-one-type.phtml', $oneType);
        $footerScripts = $this->readAttribute($this->exporter, 'footerScripts');
        $this->assertEquals($scriptOneType, $footerScripts[$oneType]);
        $this->assertArrayNotHasKey($otherType, $footerScripts);
        
        $this->exporter->setFooterScript($scriptOtherType = '/path/to/footer-other-type.phtml', $otherType);
        $footerScripts = $this->readAttribute($this->exporter, 'footerScripts');
        $this->assertEquals($scriptOtherType, $footerScripts[$otherType]);
        $this->assertEquals($scriptOneType, $footerScripts[$oneType]);
    }
    
    public function testCanClearHeaderScripts()
    {
        $this->exporter->setHeaderScript('/path/to/header-one-type.phtml', 'O');
        $this->exporter->setHeaderScript('/path/to/header-other-type.phtml', 'E');
        $this->exporter->setHeaderScript();
        $this->assertEquals(array(), $this->readAttribute($this->exporter, 'headerScripts'));
    }
    
    public function testCanClearFooterScripts()
    {
        $this->exporter->setFooterScript('/path/to/footer-one-type.phtml', 'O');
        $this->exporter->setFooterScript('/path/to/footer-other-type.phtml', 'E');
        $this->exporter->setFooterScript();
        $this->assertEquals(array(), $this->readAttribute($this->exporter, 'footerScripts'));
    }
    
    public function testAddingSameBodyScriptTwiceIsPossible()
    {
        $this->exporter->addBodyScript($body = '/path/to/body.phtml', true, $vars = array('var' => "Hello world!"))
                       ->addBodyScript($body, true, $vars);
        $this->assertCount(2, $this->readAttribute($this->exporter, 'bodyScripts'));
        $this->assertCount(2, $this->readAttribute($this->exporter, 'scriptVars'));
    }
    
    public function testAddingSameBodyHtmlSnippetTwiceIsPossible()
    {
        $this->exporter->addBodyHtml($body = '<p>Hello world!</p>')
                       ->addBodyHtml($body);
        $this->assertCount(2, $this->readAttribute($this->exporter, 'bodyScripts'));
        $this->assertEmpty($this->readAttribute($this->exporter, 'scriptVars'));
    }
    
    public function testAddingBodyScriptsAddsToHtmlBody()
    {
        $scriptPath = __DIR__ . '/TestAsset';
        $this->exporter->getRenderer()->resolver()->addPath($scriptPath);
        $this->exporter->addBodyScript($script1 = 'body-part1.phtml')
                       ->addBodyScript($script2 = 'body-part2.phtml');
        $this->assertContains(file_get_contents($scriptPath . '/' . $script1), $htmlBody = $this->exporter->getHtmlBody());
        $this->assertContains(file_get_contents($scriptPath . '/' . $script2), $htmlBody);
    }
    
    public function testAddingBodyScriptsSpecifyingVarsAddsToHtmlBody()
    {
        $scriptPath = __DIR__ . '/TestAsset';
        $this->exporter->getRenderer()->resolver()->addPath($scriptPath);
        $this->exporter->addBodyScript($script1 = 'body-part1.phtml')
                       ->addBodyScript($script2 = 'body-part2.phtml')
                       ->addBodyScript($script3 = 'body-part3.phtml', true, array('user' => $user = "Bobby Joe"));
        $this->assertContains(file_get_contents($scriptPath . '/' . $script1), $htmlBody = $this->exporter->getHtmlBody());
        $this->assertContains(file_get_contents($scriptPath . '/' . $script2), $htmlBody);
        $this->assertContains("<p>Bye, $user.</p>", $htmlBody);
    }
    
    public function testAddingBodyHtmlSnippetsAddsToHtmlBody()
    {
        $this->exporter->addBodyHtml($snippet1 = '<h1>A great title</h1>')
                       ->addBodyHtml($snippet2 = '<p>Hello world!</p>');
        $this->assertContains($snippet1, $htmlBody = $this->exporter->getHtmlBody());
        $this->assertContains($snippet2, $htmlBody);
    }
    
    public function testDefaultCssScriptIsAddedToHtmlBody()
    {
        $this->exporter->addBodyHtml($body = '<h1>A great title</h1>');
        $style = file_get_contents($this->exporter->getDefaultScriptsPath() . '/pdf.css');
        $this->assertContains($body, $htmlBody = $this->exporter->getHtmlBody());
        $this->assertContains($style, $htmlBody);
    }
    
    public function testExportingUsesRendererToRenderHeaderScripts()
    {                
        $renderer = $this->getMock('Zend\View\Renderer\PhpRenderer', array('render'));
        $renderer->expects($this->exactly(2)) // once for even pages, once for odd pages
                 ->method('render')
                 ->will($this->returnValue('content'));
                
        $this->exporter->setRenderer($renderer)
                       ->setHeaderScript('/path/to/script.phtml')
                       ->addBodyHtml($body = '<h1>A great title</h1>')
                       ->export('peu-importe.pdf', Pdf::DESTINATION_STRING);
    }
    
    public function testExportingUsesRendererToRenderFooterScripts()
    {
        $renderer = $this->getMock('Zend\View\Renderer\PhpRenderer', array('render'));
        $renderer->expects($this->exactly(2)) // once for even pages, once for odd pages
                 ->method('render')
                 ->will($this->returnValue('content'));
                
        $this->exporter->setRenderer($renderer)
                       ->setFooterScript('/path/to/script.phtml')
                       ->addBodyHtml($body = '<h1>A great title</h1>')
                       ->export('peu-importe.pdf', Pdf::DESTINATION_STRING);
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\LogicException
     * @expectedExceptionMessage Aucun script spécifié.
     */
    public function testExportingThrowsExceptionIfNoScriptSpecified()
    {
        $this->exporter->export('peu-importe.pdf');
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\LogicException
     */
    public function testExportingWithoutSpecifyingPdfFilenameThrowsException()
    {
        $this->exporter->export();
    }
    
    public function testExportingWithMemoryLimitRestoreInitialValue()
    {
        $limit = '256M'; // mettre assez car la génération éventuelle de couverture de code par PHPUnit est gourmande
        $initial = ini_get('memory_limit');
        $this->exporter->addBodyHtml($body = '<h1>A great title</h1>')
                       ->export('peu-importe.pdf', Pdf::DESTINATION_STRING, $limit); 
        $this->assertEquals($initial, ini_get('memory_limit'));
    }
    
    public function testExportingToStringReturnsString()
    {
        $result = $this->exporter->addBodyHtml($body = '<h1>A great title</h1>')
                                 ->export('peu-importe.pdf', Pdf::DESTINATION_STRING);
        $this->assertEquals('PDF content', $result);
    }
    
    public function testExportingToFileCreatesFile()
    {
        $result = $this->exporter->addBodyHtml($body = '<h1>A great title</h1>')
                                 ->export($filename = uniqid('export') . '.pdf', Pdf::DESTINATION_FILE);
        $this->assertEquals('PDF content', $result);
    }
    
    public function testExportingToBrowser()
    {
        $result = $this->exporter->addBodyHtml($body = '<h1>A great title</h1>')
                       ->export($filename = uniqid('export') . '.pdf', Pdf::DESTINATION_BROWSER);
        $this->assertNull($result);
        
        $result = $this->exporter->export($filename = uniqid('export') . '.pdf', Pdf::DESTINATION_BROWSER_FORCE_DL);
        $this->assertNull($result);
    }

    public function testSettingLogoFromLocalFileAndExportingToFileCreatesFile()
    {
        $logo = file_get_contents(__DIR__ . "/TestAsset/logo.png");

        $this
            ->whenSettingLogoAndExportingToFile($filename = uniqid('export') . '.pdf', $logo)
            ->assertFileExists($this->tempDirectoryPath . '/' . $filename);
    }

    public function testSettingLogoFromRemoteFileAndExportingToFileCreatesFile()
    {
        $logo = file_get_contents("http://gest.unicaen.fr/images/logo-ucbn-noir.png");

        $this
            ->whenSettingLogoAndExportingToFile($filename = uniqid('export') . '.pdf', $logo)
            ->assertFileExists($this->tempDirectoryPath . '/' . $filename);
    }

    private function whenSettingLogoAndExportingToFile($filename, $logo)
    {
        $this->exporter = new Pdf();
        $this->exporter
            ->addBodyHtml($body = '<h1>A great title</h1>')
            ->setLogo($logo)
            ->setExportDirectoryPath($this->tempDirectoryPath)
            ->export($filename, Pdf::DESTINATION_FILE);

        return $this;
    }
}