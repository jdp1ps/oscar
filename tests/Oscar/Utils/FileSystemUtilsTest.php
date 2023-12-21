<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 18/04/2018
 * Time: 13:39
 */

namespace Oscar\Utils;

use Oscar\Exception\OscarException;
use PHPUnit\Framework\TestCase;
use Zend\Db\Sql\Ddl\Column\Datetime;

class FileSystemUtilsTest extends TestCase
{
    protected function getFileSystemUtiles()
    {
        return FileSystemUtils::getInstance()->setVerbosityDebug(false);
    }

    public function testMkdirError1()
    {
//        $this->expectException(OscarException::class);
//        $this->getFileSystemUtiles()->mkdir('/root/pasledroit');
    }

    public function testFilePutContents()
    {
        $dir = '/tmp/' . uniqid('unit_test_oscar_');
        $file = $dir . '/' . uniqid('created_file_');
        $this->getFileSystemUtiles()->mkdir($dir);
        $this->assertTrue($this->getFileSystemUtiles()->file_put_contents($file, "foo"));
        $this->getFileSystemUtiles()->unlink($file);
        $this->getFileSystemUtiles()->rmdir($dir);
    }

//    public function testMkdirError2()
//    {
//        $this->expectException(OscarException::class);
//        $this->getFileSystemUtiles()->mkdir('/tmp/undossierquinexistepas/cree');
//    }

    public function testMkdirError3()
    {
        $meh = __FILE__ . '/nop';
        $this->expectException(OscarException::class);
        $this->getFileSystemUtiles()->mkdir($meh);
    }

//    public function testCheckDirWritable()
//    {
//        $this->expectException(OscarException::class);
//        $this->getFileSystemUtiles()->checkDirWritable('/root');
//    }

    public function testMakeAndDeleteDir()
    {
        $dir = '/tmp/' . uniqid('unit_test_oscar_');
        $this->getFileSystemUtiles()->mkdir($dir);
        $this->getFileSystemUtiles()->checkDirWritable($dir);
        $this->getFileSystemUtiles()->rmdir($dir);
        $this->expectException(OscarException::class);
        $this->getFileSystemUtiles()->checkIsDir($dir);
    }

    public function testRmdirNonDir()
    {
        $noDir = __FILE__;
        $this->expectException(OscarException::class);
        $this->getFileSystemUtiles()->mkdir($noDir);
    }

    public function testRenameFileOk()
    {
        $dir = '/tmp/' . uniqid('unit_test_oscar_');
        $from = $dir . '/from';
        $to = $dir . '/to';
        $fileFrom = $from . '/fichier';
        $fileTo = $to . '/fichier';
        $this->assertTrue($this->getFileSystemUtiles()->mkdir($dir));
        $this->assertTrue($this->getFileSystemUtiles()->mkdir($from));
        $this->assertTrue($this->getFileSystemUtiles()->mkdir($to));
        $this->assertTrue($this->getFileSystemUtiles()->file_put_contents($fileFrom, 'TEST'));
        $this->assertTrue($this->getFileSystemUtiles()->rename($fileFrom, $fileTo));

        // Suppression des fichiers
        $this->assertTrue($this->getFileSystemUtiles()->unlink($fileTo));
        $this->assertTrue($this->getFileSystemUtiles()->rmdir($from));
        $this->assertTrue($this->getFileSystemUtiles()->rmdir($to));
        $this->assertTrue($this->getFileSystemUtiles()->rmdir($dir));
    }

    public function testFileGetContents()
    {
        $dir = '/tmp/' . uniqid('unit_test_oscar_');
        $file = $dir . '/some_file_to_read';
        $this->assertTrue($this->getFileSystemUtiles()->mkdir($dir));
        $this->assertTrue($this->getFileSystemUtiles()->file_put_contents($file, 'TEST'));

        $this->assertEquals("TEST", $this->getFileSystemUtiles()->file_get_contents($file));

        // Suppression des fichiers
        $this->assertTrue($this->getFileSystemUtiles()->unlink($file));
        $this->assertTrue($this->getFileSystemUtiles()->rmdir($dir));
    }
}