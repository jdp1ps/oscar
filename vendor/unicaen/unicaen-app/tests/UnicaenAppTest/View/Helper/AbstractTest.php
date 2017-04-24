<?php
namespace UnicaenAppTest\View\Helper;

use PHPUnit_Framework_TestCase;
use Zend\View\Renderer\PhpRenderer;

/**
 * Description of AbstractTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
abstract class AbstractTest extends PHPUnit_Framework_TestCase
{
    protected $files;
    protected $helperClass;
    protected $helper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->files = __DIR__ . '/_files';
        
        $this->helper = new $this->helperClass();
        $this->helper->setView(new PhpRenderer());
    }

    /**
     * Returns the content of the expected $file
     * 
     * @param string $file
     * @return string
     */
    protected function getExpected($file)
    {
        return file_get_contents($this->files . '/expected/' . $file);
    }
}