<?php

namespace Oscar\Utils\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationLoader::class)]
class ConfigurationLoaderTest extends TestCase
{
    public function testParseFile()
    {
        $file = __DIR__ . "/config01.yml";
        $loader = new ConfigurationLoader($file);
        $settings = $loader->parseFile($file);

        $this->assertArrayHasKey('oscar', $settings);
        $this->assertArrayHasKey('parameters', $settings['oscar']);
        $this->assertEquals('foo', $settings['oscar']['parameters']['value']);
        $this->assertEquals('bar', $settings['oscar']['parameters']['othervalue']);
    }

    public function testBasic()
    {
        $file = __DIR__ . "/config01.yml";
        $loader = new ConfigurationLoader([$file]);
        $settings = $loader->load();
        $this->assertEquals(false, $settings["oscar"]["types"]["boolean_false"]);
        $this->assertEquals(true, $settings["oscar"]["types"]["boolean_true"]);
        $this->assertEquals(128, $settings["oscar"]["types"]["int_1"]);
        $this->assertEquals(-256, $settings["oscar"]["types"]["int_2"]);
    }

    public function testWithParams()
    {
        $file1 = __DIR__ . "/config03_params.yml";
        $params = __DIR__ . "/.env.example1";
        $loader = new ConfigurationLoader([$file1]);
        $loader->env(__DIR__, '.env.example1');
        $settings = $loader->load();

        var_dump($settings);

        $this->assertEquals("Chaîne", $settings["oscar"]["parameters"]["chaînes"]["in_param_1"]);
        $this->assertEquals("Chaîne avec des guillemets", $settings["oscar"]["parameters"]["chaînes"]["in_param_2"]);
        $this->assertEquals('Chaîne avec des "guillemets"', $settings["oscar"]["parameters"]["chaînes"]["in_param_3"]);
        $this->assertEquals("Chaîne avec des guillemets", $settings["oscar"]["parameters"]["chaînes"]["in_param_4"]);

        $this->assertEquals("", $settings["oscar"]["parameters"]["chaînes"]["nowhere"]);

        $this->assertEquals("Dans YAML", $settings["oscar"]["parameters"]["chaînes"]["in_yaml"]);
    }

    public function testMerged()
    {
        $file1 = __DIR__ . "/config01.yml";
        $file2 = __DIR__ . "/config01_merge.yml";
        $loader = new ConfigurationLoader([$file1, $file2]);
        $settings = $loader->load();
        $this->assertEquals(false, $settings["oscar"]["types"]["boolean_false"]);
        $this->assertEquals(true, $settings["oscar"]["types"]["boolean_true"]);
        $this->assertEquals(128, $settings["oscar"]["types"]["int_1"]);
        $this->assertEquals(-256, $settings["oscar"]["types"]["int_2"]);
        $this->assertEquals("config01_merge.yml", $settings["oscar"]["merged"]["value"]);
        $this->assertEquals("changed", $settings["oscar"]["merged"]["in"]["deep"]["change"]);
    }


    public function testPregReplaceCallback()
    {
        $file = __DIR__ . "/config02.yml";
        $loader = new ConfigurationLoader([$file]);
        $values = [
            "SIMPLE" => "SimpleValue",

            "BOOLEAN_TRUE" => true,
            "BOOLEAN_FALSE" => false,

            "FLOAT_3_14" => 3.14,
            "FLOAT_M_3_14" => -3.14,
            "INT_256" => 256,
            "INT_M_256" => -256,

            "ARRAY1" => "1,2,3",
            "ARRAY2" => "stephane@mail.com,jean-baptiste@mail.com,karin@mail.com",
        ];

        // --- Valeur simple
        $this->assertEquals("YearBaby", $loader->pregReplaceCallback("YearBaby", $values), "Valeur simple : Chaîne simple");

        // --- Variable non définie
        $this->assertEquals(
            "",
            $loader->pregReplaceCallback("%env(UNKNOW)%", $values),
            "Les paramètres manquants optionnels sont des chaînes vides"
        );

        // --- Variables non-définie typées
        $this->assertEquals(
            false,
            $loader->pregReplaceCallback("%env(bool:UNKNOW)%", $values),
            "Les paramètres manquants typé bool optionnels sont des FALSE"
        );
        $this->assertEquals(
            0,
            $loader->pregReplaceCallback("%env(int:UNKNOW)%", $values),
            "Les paramètres manquants typé int optionnels sont des 0"
        );
        $this->assertEquals(
            0.0,
            $loader->pregReplaceCallback("%env(float:UNKNOW)%", $values),
            "Les paramètres manquants typé float optionnels sont des 0.0"
        );

        $getArray = $loader->pregReplaceCallback("%env(array:UNKNOW)%", $values);
        $this->assertTrue(is_array($getArray), "Les paramètres manquants typé array optionnels sont des tableaux");
        $this->assertEquals(0, count($getArray), "Les paramètres manquants typé array optionnels sont des tableaux vides");


        // Variable définie
        $this->assertEquals("SimpleValue", $loader->pregReplaceCallback("%env(SIMPLE)%", $values));

        // Booleen
        $this->assertTrue($loader->pregReplaceCallback("%env(bool:BOOLEAN_TRUE)%", $values), 'Type Boolean forcé (true)');
        $this->assertFalse($loader->pregReplaceCallback("%env(bool:BOOLEAN_FALSE)%", $values), 'Type Boolean forcé (false)');

        // Float
        $this->assertEquals(
            3.14,
            $loader->pregReplaceCallback("%env(bool:FLOAT_3_14)%", $values),
            'Type Float forcé'
        );
        $this->assertEquals(
            -3.14,
            $loader->pregReplaceCallback("%env(bool:FLOAT_M_3_14)%", $values),
            'Type Float forcé'
        );

        // Int
        $this->assertEquals(256, $loader->pregReplaceCallback("%env(bool:INT_M_256)%", $values), 'Type Int forcé');
        $this->assertEquals(-256, $loader->pregReplaceCallback("%env(bool:INT_256)%", $values), 'Type Int forcé');

        // array
        $this->assertEquals(
            [1, 2, 3],
            $loader->pregReplaceCallback("%env(array:ARRAY1)%", $values),
            'Type Array forcé'
        );
        $this->assertEquals(
            ["stephane@mail.com", "jean-baptiste@mail.com", "karin@mail.com"],
            $loader->pregReplaceCallback("%env(array:ARRAY2)%", $values),
            'Type Array forcé'
        );

        // Type inconnu
        $var = '%env(unknow:UNKNOW_TYPE_OPTIONNAL)%';
        try {
            $value = $loader->pregReplaceCallback($var, $values);
            $this->fail("Type inconnue non-détécté");
        } catch (ConfigurationLoaderException $e) {
            $this->assertEquals(
                "Type de paramètre 'unknow' inconnu dans '$var'",
                $e->getMessage(),
                "Message d'erreur des types explicite incorrect"
            );
        }

        // Variable obligatoire non définie
        $tests = [
            '%env(!:UNTYPED)%' => "Expected parameter 'UNTYPED' is missing",
            '%env(!bool:UNKNOW_BOOL)%' => "Expected parameter 'UNKNOW_BOOL'(bool) is missing",
            '%env(!string:UNKNOW_PARAM)%' => "Expected parameter 'UNKNOW_PARAM'(string) is missing",
            '%env(!array:UNKNOW_PARAM)%' => "Expected parameter 'UNKNOW_PARAM'(array) is missing",
            '%env(!float:UNKNOW_PARAM)%' => "Expected parameter 'UNKNOW_PARAM'(float) is missing",
            '%env(!int:UNKNOW_PARAM)%' => "Expected parameter 'UNKNOW_PARAM'(int) is missing",
        ];

        foreach ($tests as $input => $errorExpected) {
            try {
                $value = $loader->pregReplaceCallback($input, $values);
                $this->fail("Paramètre $input requis non-détécté");
            } catch (ConfigurationLoaderException $e) {
                $this->assertEquals($errorExpected, $e->getMessage(), "Message d'erreur explicite incorrect");
            }
        }
    }
}
