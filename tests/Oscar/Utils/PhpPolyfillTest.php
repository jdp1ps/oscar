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

class PhpPolyfillTest extends TestCase
{
    public function testJsonDecode()
    {
        $data = "toto";
        $this->assertEquals('"toto"', PhpPolyfill::jsonEncode($data));

        $data = ["an", 'array'];
        $this->assertEquals('["an","array"]', PhpPolyfill::jsonEncode($data));

        $data = ['foo' => "an", 'bar' => 'object'];
        $this->assertEquals('{"foo":"an","bar":"object"}', PhpPolyfill::jsonEncode($data));
    }

    public function testJsonEncode()
    {
        $data = '{"toto"';
        try {
            PhpPolyfill::jsonDecode($data);
            $this->fail("Doit lever une exception");
        } catch (\Exception $e) {
            $this->assertEquals("Can't decode data to JSON : Syntax error", $e->getMessage());
        }

        $data = '{"foo":{"bar":{"internal":true}}}';
        try {
            $result = PhpPolyfill::jsonDecode($data, true, JSON_PRETTY_PRINT);
            $this->assertEquals(true, $result['foo']['bar']['internal']);
        } catch (\Exception $e) {
            $this->assertEquals("Can't decode data to JSON : Syntax error", $e->getMessage());
        }

        try {
            $result = PhpPolyfill::jsonDecode($data, true, 1);
            $this->fail("Doit lever une exception");
        } catch (\Exception $e) {
            $this->assertEquals("Can't decode data to JSON : Maximum stack depth exceeded", $e->getMessage());
        }
    }
}