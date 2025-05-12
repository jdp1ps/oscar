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
        $loader = new ConfigurationLoader([$file]);
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
        $settings = $loader->load(true);
        $this->assertEquals(false, $settings["oscar"]["types"]["boolean_false"]);
        $this->assertEquals(true, $settings["oscar"]["types"]["boolean_true"]);
        $this->assertEquals(128, $settings["oscar"]["types"]["int_1"]);
        $this->assertEquals(-256, $settings["oscar"]["types"]["int_2"]);
    }

    public function testWithParams()
    {
        $file1 = __DIR__ . "/config03_params.yml";
        $loader = new ConfigurationLoader([$file1]);
        $loader->env(__DIR__, '.env.example1');
        $settings = $loader->load(true);

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
        $settings = $loader->load(true);
        $this->assertEquals(false, $settings["oscar"]["types"]["boolean_false"]);
        $this->assertEquals(true, $settings["oscar"]["types"]["boolean_true"]);
        $this->assertEquals(128, $settings["oscar"]["types"]["int_1"]);
        $this->assertEquals(-256, $settings["oscar"]["types"]["int_2"]);
        // Le merge le "merge" pas (mécanique non-implémentées)
        // > Les clefs ne sont pas remplacées
        // $this->assertEquals("config01_merge.yml", $settings["oscar"]["merged"]["value"]);
        // $this->assertEquals("changed", $settings["oscar"]["merged"]["in"]["deep"]["change"]);
    }

    public function testPregReplaceCallbackBoolean()
    {
        $file = __DIR__ . "/config02.yml";
        $loader = new ConfigurationLoader([$file]);


        // --- Valeur simple
        $value = "%env(bool:boolean_value)%";
        $this->assertTrue(
            $loader->pregReplaceCallback($value, ['boolean_value' => 'true']),
            "la chaîne 'true' devient TRUE"
        );

        $value = "%env(bool:boolean_value)%";
        $this->assertFalse(
            $loader->pregReplaceCallback($value, ['boolean_value' => 'false']),
            "la chaîne 'false' devient FALSE"
        );

        $value = "%env(bool:boolean_value)%";
        $this->assertTrue(
            $loader->pregReplaceCallback($value, ['boolean_value' => '1']),
            "la chaîne '1' devient TRUE"
        );

        $value = "%env(bool:boolean_value)%";
        $this->assertFalse(
            $loader->pregReplaceCallback($value, ['boolean_value' => '0']),
            "la chaîne '0' devient FALSE"
        );

        $value = "%env(bool:boolean_value)%";
        $this->assertTrue(
            $loader->pregReplaceCallback($value, ['boolean_value' => 'on']),
            "la chaîne 'on' devient TRUE"
        );

        $value = "%env(bool:boolean_value)%";
        $this->assertFalse(
            $loader->pregReplaceCallback($value, ['boolean_value' => 'off']),
            "la chaîne 'off' devient FALSE"
        );
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
        $value = "YearBaby";
        $this->assertEquals(
            "YearBaby",
            $loader->pregReplaceCallback($value, $values),
            "Valeur simple : Chaîne simple");

        // --- Variable non définie
        $value = "%env(UNKNOW)%";
        $this->assertEquals(
            "",
            $loader->pregReplaceCallback($value, $values),
            "Les paramètres manquants optionnels sont des chaînes vides"
        );

        // --- Variables non-définie typées
        $value = "%env(bool:UNKNOW)%";
        $this->assertEquals(
            false,
            $loader->pregReplaceCallback($value, $values),
            "Les paramètres manquants typé bool optionnels sont des FALSE"
        );


        $value = "%env(int:UNKNOW)%";
        $this->assertEquals(
            0,
            $loader->pregReplaceCallback($value, $values),
            "Les paramètres manquants typé int optionnels sont des 0"
        );

        $value = "%env(float:UNKNOW)%";
        $this->assertEquals(
            0.0,
            $loader->pregReplaceCallback($value, $values),
            "Les paramètres manquants typé float optionnels sont des 0.0"
        );

        $value = "%env(array:UNKNOW)%";
        $getArray = $loader->pregReplaceCallback($value, $values);
        $this->assertTrue(is_array($getArray), "Les paramètres manquants typé array optionnels sont des tableaux");
        $this->assertEquals(
            0,
            count($getArray),
            "Les paramètres manquants typé array optionnels sont des tableaux vides"
        );


        // Variable définie
        $value = "%env(SIMPLE)%";
        $this->assertEquals(
            "SimpleValue",
            $loader->pregReplaceCallback($value, $values)
        );

        // Booleen
        $value = "%env(bool:BOOLEAN_TRUE)%";
        $this->assertTrue(
            $loader->pregReplaceCallback($value, $values),
            'Type Boolean forcé (true)'
        );
        $value = "%env(bool:BOOLEAN_FALSE)%";
        $this->assertFalse(
            $loader->pregReplaceCallback($value, $values),
            'Type Boolean forcé (false)'
        );

        // Float
        $value = "%env(bool:FLOAT_3_14)%";
        $this->assertEquals(
            3.14,
            $loader->pregReplaceCallback($value, $values),
            'Type Float forcé'
        );
        $value = "%env(bool:FLOAT_M_3_14)%";
        $this->assertEquals(
            -3.14,
            $loader->pregReplaceCallback($value, $values),
            'Type Float forcé'
        );

        // Int
        $value = "%env(bool:INT_M_256)%";
        $this->assertEquals(256, $loader->pregReplaceCallback($value, $values), 'Type Int forcé');

        $value = "%env(bool:INT_256)%";
        $this->assertEquals(-256, $loader->pregReplaceCallback($value, $values), 'Type Int forcé');


        // array
        $value = "%env(array:ARRAY1)%";
        $this->assertEquals(
            [1, 2, 3],
            $loader->pregReplaceCallback($value, $values),
            'Type Array forcé'
        );

        $value = "%env(array:ARRAY2)%";
        $this->assertEquals(
            ["stephane@mail.com", "jean-baptiste@mail.com", "karin@mail.com"],
            $loader->pregReplaceCallback($value, $values),
            'Type Array forcé'
        );


        // Type inconnu
        $msg = $var = '%env(unknow:UNKNOW_TYPE_OPTIONNAL)%';
        try {
            $value = $loader->pregReplaceCallback($var, $values);
            $this->fail("Type inconnue non-détécté");
        } catch (ConfigurationLoaderException $e) {
            $this->assertEquals(
                "Type de paramètre 'unknow' inconnu dans '$msg'",
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
