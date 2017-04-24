<?php
namespace UnicaenAppTest;

use DateTime;
use PHPUnit_Framework_TestCase;
use UnicaenApp\Util;

/**
 * Description of UtilTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UtilTest extends PHPUnit_Framework_TestCase
{
    public static $functionCallbacks;
   
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        self::$functionCallbacks = array();
    }
    
    public function getCollectionAsOptionsTestDataset()
    {
        $author1 = new Author(10, "Bob");
        $author2 = new Author(11, "Joe");
        $post1 = new Post(1, "Bonjour!",      $author1, null);
        $post2 = new Post(2, "Hello, world!", $author2, null);
        $thing1 = new Thing('thing1');
        $thing2 = new Thing('thing2');
        return array(
            /**
             * Post : has getId() method
             */
            array(
                new \ArrayIterator(array()),
                false,
                null,
                null,
                null,
                array(),
            ),
            array(
                new \ArrayIterator(array($post1, $post2)),
                false,
                null,
                null,
                null,
                array(
                    $post1->getId() => (string) $post1, 
                    $post2->getId() => (string) $post2,
                ),
            ),
            array(
                new \ArrayIterator(array($post1, $post2)),
                false,
                $attributeForValues = 12, // invalid param <=> toString
                null,
                null,
                array(
                    $post1->getId() => (string) $post1, 
                    $post2->getId() => (string) $post2,
                ),
            ),
            array(
                new \ArrayIterator(array($post1, $post2)),
                false,
                null,
                $attributeForKeys = 'getPostId', // invalid method name <=> getId
                null,
                array(
                    $post1->getId() => (string) $post1, 
                    $post2->getId() => (string) $post2,
                ),
            ),
            array(
                new \ArrayIterator(array($post1, $post2)),
                false,
                $attributeForValues = 'toString',
                $attributeForKeys = 'id',
                null,
                array(
                    $post1->getId() => (string) $post1, 
                    $post2->getId() => (string) $post2,
                ),
            ),
            array(
                new \ArrayIterator(array($post1, $post2)),
                $sort = true, // sort on value
                null,
                null,
                null,
                array( 
                    $post2->getId() => (string) $post2,
                    $post1->getId() => (string) $post1,
                ),
            ),
            array(
                new \ArrayIterator(array($post1, $post2)),
                $sort = false,
                $attributeForValues = 'message',
                null,
                null,
                array( 
                    $post1->getId() => $post1->message,
                    $post2->getId() => $post2->message,
                ),
            ),
            array(
                new \ArrayIterator(array($post1, $post2)),
                $sort = false,
                $attributeForValues = 'getId',
                $attributeForKeys = 'message',
                null,
                array( 
                    $post1->message => $post1->getId(),
                    $post2->message => $post2->getId(),
                ),
            ),
            array(
                new \ArrayIterator(array($post1, $post2)),
                $sort = false,
                $attributeForValues = 'message',
                $attributeForKeys = 'getId',
                $keysPrefix = 'div_',
                array( 
                    'div_' . $post1->getId() => $post1->message,
                    'div_' . $post2->getId() => $post2->message,
                ),
            ),
            array(
                new \ArrayIterator(array($post1, $post2)),
                false,
                $attributeForValues = array('getId', 'message'),
                null,
                null,
                array(
                    $post1->getId() => $post1->getId() . ' - ' . $post1->message, 
                    $post2->getId() => $post2->getId() . ' - ' . $post2->message, 
                ),
            ),
            array(
                new \ArrayIterator(array($post1, $post2)),
                false,
                $attributeForValues = "Post {getId} : {message} [From {author.name}]",
                null,
                null,
                array(
                    $post1->getId() => "Post {$post1->getId()} : {$post1->message} [From {$post1->author->name}]", 
                    $post2->getId() => "Post {$post2->getId()} : {$post2->message} [From {$post2->author->name}]", 
                ),
            ),
            array(
                new \ArrayIterator(array($post1, $post2)),
                false,
                $attributeForValues = function($element) { return "<div id=\"{$element->getId()}\">{$element->message}</div>"; },
                null,
                null,
                array(
                    $post1->getId() => "<div id=\"{$post1->getId()}\">{$post1->message}</div>", 
                    $post2->getId() => "<div id=\"{$post2->getId()}\">{$post2->message}</div>", 
                ),
            ),
            /**
             * Author : has no getId() method, but has getUid() method
             */
            array(
                new \ArrayIterator(array($author1, $author2)),
                false,
                null, // 'uid' will be used instead of 'id'
                null,
                null,
                array(
                    $author1->getUid() => (string) $author1, 
                    $author2->getUid() => (string) $author2, 
                ),
            ),
            /**
             * Thing : has no getId() neither getUid() method
             */
            array(
                new \ArrayIterator(array($thing1, $thing2)),
                false,
                null, 
                null, // collection indexes will be used
                null,
                array(
                    0 => ucfirst((string) $thing1), 
                    1 => ucfirst((string) $thing2), 
                ),
            ),
        );
    }
    
    /**
     * @dataProvider getCollectionAsOptionsTestDataset
     * @param \Iterator $collection
     * @param bool $sort
     * @param string $attributeForValues
     * @param string $attributeForKeys
     * @param string $keysPrefix
     * @param array $expectedResult
     */
    public function testCollectionAsOptionsReturnsCorrectArray(
            \Iterator $collection,
            $sort,
            $attributeForValues,
            $attributeForKeys,
            $keysPrefix,
            $expectedResult)
    {
        $result = Util::collectionAsOptions($collection, $sort, $attributeForValues, $attributeForKeys, $keysPrefix);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getDatasetForTokenReplacedString()
    {
        return [
            [
                ['dateModification' => 'mardi 14 juillet 2015', 'intervenant' => "Bertrand GAUTHIER"],
                "Les données personnelles de l'intervenant ont été saisies.",
                "Les données personnelles de l'intervenant ont été saisies.",
            ],
            [
                ['dateModification' => 'mardi 14 juillet 2015', 'intervenant' => "Bertrand GAUTHIER"],
                "Les données personnelles de {intervenant} ont été saisies.",
                "Les données personnelles de Bertrand GAUTHIER ont été saisies.",
            ],
            [
                ['dateModification' => 'mardi 14 juillet 2015', 'intervenant' => "Bertrand GAUTHIER"],
                "Les données personnelles de {intervenant} ont été saisies le {dateModification}.",
                "Les données personnelles de Bertrand GAUTHIER ont été saisies le mardi 14 juillet 2015.",
            ],
            [
                ['dateModification' => 'mardi 14 juillet 2015', 'intervenant' => "Bertrand GAUTHIER"],
                "Les données personnelles de {intervenant} ont été saisies le {dateModification} par {auteur}.",
                "Les données personnelles de Bertrand GAUTHIER ont été saisies le mardi 14 juillet 2015 par {auteur}.",
            ],
        ];
    }

    /**
     * @dataProvider getDatasetForTokenReplacedString
     * @param $replacements
     * @param $inputString
     * @param $expectedString
     */
    public function testTokenReplacedString($replacements, $inputString, $expectedString)
    {
        $result = Util::tokenReplacedString($inputString, $replacements);

        $this->assertEquals($expectedString, $result);
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\LogicException
     */
    public function testGetObjectAttributeFromPathThrowsExceptionIfInvalidObjectSpecified()
    {
        Util::getObjectAttributeFromPath('no-object-object', 'path');
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\LogicException
     */
    public function testGetObjectAttributeFromPathThrowsExceptionIfInvalidPathSpecified()
    {
        Util::getObjectAttributeFromPath(new \stdClass, 12);
    }
    
    public function getValidObjectPathAndExpectedResult()
    {
        $parent = new Post(10, "Hello, world!", new Author(45, "Bob"),  null);
        $post   = new Post(15, "Bye!",          new Author(23, "Jack"), $parent);
        return array(
            array(
                $post,
                'message',
                $post->message,
            ),
            array(
                $post,
                'toString',
                (string) $post,
            ),
            array(
                $post,
                'author.name',
                $post->author->name,
            ),
            array(
                $post,
                'author.toString',
                (string) $post->author,
            ),
            array(
                $post,
                'parent.author.getUid',
                $post->parent->author->getUid(),
            ),
            array(
                $post,
                'parent.author.toString',
                (string) $post->parent->author,
            ),
        );
    }
    
    /**
     * @dataProvider getValidObjectPathAndExpectedResult
     * @param mixed $object
     * @param string $path
     * @param string $expectedResult
     */
    public function testGetObjectAttributeFromPath($object, $path, $expectedResult)
    {
        $result = Util::getObjectAttributeFromPath($object, $path);
        $this->assertEquals($expectedResult, $result);
    }
    
    public function getInvalidObjectPath()
    {
        $post = new Post(15, "Cheers!", new Author(123, "Jack")); // NB: no parent post
        return array(
            'unknown-terminal-attribute-1' => array(
                $post,
                'timestamp',
            ),
            'unknown-terminal-attribute-2' => array(
                $post,
                'author.email',
            ),
            'not-object-not-terminal-attribute' => array(
                $post,
                'message.length', // message is scalar
            ),
            'empty-not-terminal-attribute' => array(
                $post,
                'parent.message', // parent post is null
            ),
        );
    }
    
    /**
     * @dataProvider getInvalidObjectPath
     * @expectedException \UnicaenApp\Exception\LogicException
     * @param mixed $object
     * @param string $path
     */
    public function testGetObjectAttributeFromPathThrowsExceptionIfInvalidPathAttributeSpecified($object, $path)
    {
        Util::getObjectAttributeFromPath($object, $path);
    }
    
    public function provideDateTime()
    {
        return array(
            'a' => array(null),
            'b' => array(DateTime::createFromFormat('d/m/Y H:i:s', '31/12/2012 23:59:59')),
            'c' => array(DateTime::createFromFormat('d/m/Y', '01/01/2013')),
        );
    }
    
    /**
     * @dataProvider provideDateTime
     */
    public function testGenerateStringTimestamp($dateTime)
    {
        $str = Util::generateStringTimestamp($dateTime);
        $dateTime = $dateTime ?: new DateTime();
        $this->assertEquals($dateTime->format('Ymd_His'), $str);
    }
    
    public function testTopChrono()
    {
        $this->expectOutputRegex('/chrono: 0/');
        Util::topChrono();
        
        $message = 'Hello';
        $this->expectOutputRegex('/' . $message . ': 0/');
        Util::topChrono($message, true);
        
        $message = 'Hola';
        $patternFloat = '[+-]?\.?\d+\.?\d*(?:[Ee][+-]?\d+)?';
        $this->expectOutputRegex('/' . $message . ': (' . $patternFloat . ')/');
        Util::topChrono($message);
        
        $message = 'Bonjour';
        $this->expectOutputRegex('/' . $message . ': 0/');
        Util::topChrono($message, true);
        
        $message = 'Guten tag';
        $this->expectOutputRegex('/' . $message . ': (' . $patternFloat . ')/');
        Util::topChrono($message);
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\RuntimeException
     */
    public function testRemoveFileThrowsExceptionIfFileDoesNotExist()
    {
        Util::removeFile('unexisting_file');
        $this->fail("Exception non levée malgré l'inexistence du fichier cible.");
    }
    
    public function testRemoveFileReturnsFalseIfFileUnlinkFails()
    {
        $targetDir = $this->createTempDirectory();
        file_put_contents($file = $targetDir . '/' . uniqid('UnicaenTest') . '.txt', "Hello World!" . PHP_EOL);
        
        self::$functionCallbacks['unlink'] = function($filename) { return false; };
        $result = Util::removeFile($file);
        $this->assertFalse($result);
    }
    
    public function testCanRemoveFile()
    {
        $targetDir = $this->createTempDirectory();
        file_put_contents($file = $targetDir . '/' . uniqid('UnicaenTest') . '.txt', "Hello World!" . PHP_EOL);
                
        $result = Util::removeFile($file);
        $this->assertFileNotExists($file);
        $this->assertTrue($result);
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\RuntimeException
     */
    public function testRemoveDirectoryThrowsExceptionIfCannotOpenDirectory()
    {
        $targetDir = $this->createTempDirectory();
        file_put_contents($file = $targetDir . '/' . uniqid('UnicaenTest') . '.txt', "Hello World!" . PHP_EOL);
        
        self::$functionCallbacks['opendir'] = function($path) { return false; };
        Util::removeFile($targetDir);
    }
    
    public function testRemoveDirectoryReturnsFalseIfAnyContentFileUnlinkFails()
    {
        $targetDir = $this->createTempDirectory();
        file_put_contents($file = $targetDir . '/' . uniqid('UnicaenTest') . '.txt', "Hello World!" . PHP_EOL);
        
        self::$functionCallbacks['unlink'] = function($filename) {
            if ($filename !== $targetDir) {
                // s'il s'agit d'un fichier situé dans un sous-répertoire du répertoire à supprimer, on simule un échec
                return false;
            }
            return \unlink($filename);
        };
        $result = Util::removeFile($targetDir);
        $this->assertFalse($result);
    }
    
    public function testCanRemoveDirectory()
    {
        $targetDir = $this->createTempDirectory();
        file_put_contents($file = $targetDir . '/' . uniqid('UnicaenTest') . '.txt', "Hello World!" . PHP_EOL);
        
        $result = Util::removeFile($targetDir);
        $this->assertFileNotExists($targetDir);
        $this->assertTrue($result);
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\RuntimeException
     */
    public function testZipThrowsExceptionIfZipExtensionIsNotLoaded()
    {
        self::$functionCallbacks['extension_loaded'] = function($name) { return false; };
        Util::zip('never_mind', 'never_mind');
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\RuntimeException
     */
    public function testZipThrowsExceptionIfSourceDosNotExist()
    {
        Util::zip('unexisting_source', 'never_mind');
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\RuntimeException
     */
    public function testZipThrowsExceptionIfDestinationCreationFails()
    {
        if (!extension_loaded('zip')) {
            $this->markTestIncomplete("Extension Zip non installée.");
        }
        
        $targetDir = $this->createTempDirectory();
        
        $source = $targetDir . '/' . 'file1.txt';
        $dest   = $targetDir . '/' . 'destination-is-directory';
        mkdir($dest);
        
        Util::zip($source, $dest);
    }
    
    public function testCanZipFile()
    {
        if (!extension_loaded('zip')) {
            $this->markTestIncomplete("Extension Zip non installée.");
        }
        
        $targetDir = $this->createTempDirectory();
        
        Util::zip($file1 = $targetDir . '/file1.txt', $zipfile = $file1 . '.zip');
        $this->assertFileExists($zipfile);
        
        Util::removeFile($targetDir);
    }
    
    public function testCanZipDirectory()
    {
        if (!extension_loaded('zip')) {
            $this->markTestIncomplete("Extension Zip non installée.");
        }
        
        $targetDir = $this->createTempDirectory();
        
        Util::zip($targetDir, $zipfile = $targetDir . '.zip');
        $this->assertFileExists($zipfile);
        
        Util::removeFile($targetDir);
        unlink($zipfile);
    }
    
    public static function createTempDirectory()
    {
        $tmpDir = sys_get_temp_dir();
        if (!$tmpDir) {
            self::markTestIncomplete("Impossible de déterminer le répertoire temporaire du système.");
        }
        do { $dir = $tmpDir . '/' . uniqid('UnicaenTest'); } while (file_exists($dir));
        if (!@mkdir($dir)) {
            self::markTestIncomplete("Impossible de créer le répertoire de test '$dir'.");
        }
        $content = str_repeat("Hello World!" . PHP_EOL, 100);
        $file1 = $dir . '/file1.txt';
        $file2 = $dir . '/file2.txt';
        file_put_contents($file1, $content);
        file_put_contents($file2, $content);
        if (@mkdir($subdir = $dir . '/subdir')) {
            $file3 = $subdir . '/file3.txt';
            file_put_contents($file3, $content);
        }
        return $dir;
    }
    
    public function provideStringsToTruncate()
    {
        return array(
            array('', 10, '...', ''),
            array('12345', 0, '...', '...'),
            array('12345', 2, '...', '...'),
            array('123456789 ABCDEF', 60, '...', '123456789 ABCDEF'),
            array('123456789 ABCDEF', 9,  '...', '123456789...'),
            array('123456789 ABCDEF', 10, '...', '123456789...'),
            array('123456789 ABCDEF', 12, '...', '123456789...'),
            array('123456789 ABCDEF GHIJ', 18, '...', '123456789 ABCDEF...'),
        );
    }
    
    /**
     * 
     * @param string $string
     * @param int $length
     * @param string $appended
     * @param string $expected
     * @dataProvider provideStringsToTruncate
     */
    public function testTruncateString($string, $length, $appended, $expected)
    {
        $this->assertEquals($expected, Util::truncatedString($string, $length, $appended));
    }
    
    public function provideFloatsToFormat()
    {
        return array(
            array(0,          \NumberFormatter::DECIMAL,  2, '0,00'),
            array(1234,       \NumberFormatter::DECIMAL,  2, '1 234,00'),
            array(12.345,     \NumberFormatter::DECIMAL,  1, '12,3'),
            array(1234.5678,  \NumberFormatter::DECIMAL,  3, '1 234,568'),
            array(1234.5678,  \NumberFormatter::CURRENCY, 2, '1 234,57 €'),
            array(-1234.5678, \NumberFormatter::CURRENCY, 2, '-1 234,57 €'),
        );
    }
    
    /**
     * 
     * @param mixed $value
     * @param int $style
     * @param int $fractionDigits
     * @param string $expected
     * @dataProvider provideFloatsToFormat
     */
    public function testFormattedFloat($value, $style, $fractionDigits, $expected)
    {
        $this->assertEquals($expected, Util::formattedFloat($value, $style, $fractionDigits));
    }
    
    public function provideDataToBeConvertedAsBytes()
    {
        return array(
            array(7, 7),
            array(' 7 ', 7),
            array(' 7K ', 7*1024),
            array('7', 7),
            array('7K', 7*1024),
            array('7k', 7*1024),
            array('7M', 7*1024*1024),
            array('7m', 7*1024*1024),
            array('7G', 7*1024*1024*1024),
            array('7g', 7*1024*1024*1024),
        );
    }
    
    /**
     * 
     * @param mixed $val
     * @param int $expected
     * @dataProvider provideDataToBeConvertedAsBytes
     */
    public function testConvertAsBytes($val, $expected)
    {
        $this->assertEquals($expected, Util::convertAsBytes($val));
    }
}

class Post
{
    protected $id;
    public $message;
    public $parent;
    public $author;
    public function __construct($id, $message, Author $author, Post $parent = null)
    {
        $this->id      = $id;
        $this->message = $message;
        $this->author  = $author;
        $this->parent  = $parent;
    }
    public function __toString()
    {
        return sprintf("N°%s, %s : %s", $this->id, $this->author, $this->message);
    }
    public function getId()
    {
        return $this->id;
    }
}

class Author
{
    protected $uid;
    public $name;
    public function __construct($uid, $name)
    {
        $this->uid  = $uid;
        $this->name = $name;
    }
    public function __toString()
    {
        return $this->name;
    }
    public function getUid()
    {
        return $this->uid;
    }
}

class Person extends \UnicaenApp\Entity\Ldap\People
{
    protected $id;
    public function __construct(array $data = array())
    {
        parent::__construct($data);
        $this->id = $this->processDataValue('id');
    }
}

class Thing
{
    public $name;
    public function __construct($name)
    {
        $this->name = $name;
    }
    public function __toString()
    {
        return $this->name;
    }
}

/**
 * Ce qui suit permet de redéfinir des fonctions PHP standards utilisées par la classe Util
 * afin de faciliter les tests.
 * NB: le namespace doit être le même que celui de la classe Util.
 */
namespace UnicaenApp;

function extension_loaded($name)
{
    $callback = array_key_exists($key = 'extension_loaded', (array) \UnicaenAppTest\UtilTest::$functionCallbacks) ?
            \UnicaenAppTest\UtilTest::$functionCallbacks[$key] :
            null;
    if (is_callable($callback)) {
        return $callback($name);
    }
    return \extension_loaded($name);
}

function unlink($filename)
{
    $callback = array_key_exists($key = 'unlink', (array) \UnicaenAppTest\UtilTest::$functionCallbacks) ?
            \UnicaenAppTest\UtilTest::$functionCallbacks[$key] :
            null;
    if (is_callable($callback)) {
        return $callback($name);
    }
    return \unlink($filename);
}

function opendir($path)
{
    $callback = array_key_exists($key = 'opendir', (array) \UnicaenAppTest\UtilTest::$functionCallbacks) ?
            \UnicaenAppTest\UtilTest::$functionCallbacks[$key] :
            null;
    if (is_callable($callback)) {
        return $callback($name);
    }
    return \opendir($path);
}