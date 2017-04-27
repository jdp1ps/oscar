<?php
namespace UnicaenAppTest\Test\Constraint;

use PHPUnit_Framework_ExpectationFailedException;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_TestFailure;
use UnicaenApp\Test\Constraint\ZipFileValid;
use UnicaenApp\Util;
use UnicaenAppTest\UtilTest;

/**
 * Description of ZipFileValidTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ZipFileValidTest extends PHPUnit_Framework_TestCase
{
    protected $constraint;
    protected $filePath;
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        if (!extension_loaded('zip')) {
            $this->markTestSkipped("L'extension Zip doit être chargée pour ces tests.");
        }
        $this->constraint = new ZipFileValid();
    }
    
    protected function tearDown()
    {
        if ($this->filePath && file_exists($this->filePath)) {
            Util::removeFile($this->filePath);
        }
    }
    
    public function testConstraintCount()
    {
        $this->assertEquals(1, count($this->constraint));
    }
    
    public function testEvaluateValidZipFileReturnsTrue()
    {
        $targetDir = UtilTest::createTempDirectory();
        Util::zip($targetDir, $this->filePath = $targetDir . '.zip');
        Util::removeFile($targetDir);
        $this->assertTrue($this->constraint->evaluate($this->filePath, '', true));
        $this->assertEquals('zip file is valid', $this->constraint->toString());
    }
    
    public function testEvaluateInvalidZipFileReturnsFalse()
    {
        if (!file_put_contents($this->filePath = sys_get_temp_dir() . '/invalid.zip', "Coucou!")) {
            $this->markTestSkipped("Impossible de créer le fichier de test '$this->filePath'.");
        }
        
        try {
            $this->constraint->evaluate($this->filePath);
        }
        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that zip file "$this->filePath" is valid.

EOF
                ,
                PHPUnit_Framework_TestFailure::exceptionToString($e)
            );
            return;
        }
        $this->fail();
    }
}