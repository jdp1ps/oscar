<?php

namespace Oscar\Utils\Config;

use Dotenv\Dotenv;
use Symfony\Component\Yaml\Yaml;

class ConfigurationLoader
{

    private array $env_parameters = [];

    /** @var string[]  */
    private array $yamlFilePaths = [];

    /**
     * @param array $yamlFilePaths Chemins des fichiers YAML à fusionner
     */
    public function __construct(array $yamlFilePaths)
    {
        $this->yamlFilePaths = $yamlFilePaths;
    }

    /**
     * Chargement de la configuration.
     *
     * @return array
     * @throws ConfigurationLoaderException
     */
    public function load() :array
    {
        $merges = [];
        foreach ($this->yamlFilePaths as $filePath) {
            $fileSettings = Yaml::parseFile($filePath);
            $merges = array_replace_recursive($merges, $fileSettings);
        }

        array_walk_recursive(/**
         * @throws ConfigurationLoaderException
         */            $merges,
            function (&$value) {
                $this->pregReplaceCallback($value, $this->env_parameters);
            }
        );

        return $merges;
    }

    /**
     * Chargement d'un fichier .env
     *
     * @param $paths string Dossier du/des fichiers .env
     * @param $name string Nom spécifique du fichier à charger
     * @return void
     */
    public function env($paths, $name = null) :void
    {
        Dotenv::createImmutable($paths, $name)->load();
        $this->env_parameters = $_ENV;
    }

    /**
     * Charge le fichier YAML.
     *
     * @param string $filePath
     * @return array
     */
    public function parseFile(string $filePath) :array
    {
        return Yaml::parseFile($filePath);
    }

    const REGEX = '/%env\(((!)?(\w*):)?(.*)\)%/m';

    /**
     * @throws ConfigurationLoaderException
     */
    public function pregReplaceCallback(?string &$value, array $values) :mixed
    {
        if( $value === null ){
            return null;
        }
        if (preg_match(self::REGEX, $value, $matches)) {
            $input = $matches[0];
            $required = ($matches[2] === '!');
            $name = $matches[4];
            $type = $matches[3];

            $inValues = array_key_exists($name, $values);
            if ($inValues) {
                $value = $values[$name];
            } else {
                if ($required) {
                    $withType = $type ? "($type)": "";
                    throw new ConfigurationLoaderException("Expected parameter '$name'$withType is missing");
                } else {
                    $value = "";
                }
            }

            switch ($type) {
                case 'bool':
                    $value = boolval($value);
                    break;
                case 'string':
                    $value = strval($value);
                    break;
                case 'int':
                    $value = intval($value);
                    break;
                case 'float':
                    $value = floatval($value);
                    break;
                case 'array':
                    $value = $value !== "" ? explode(',', $value) : [];
                    break;

                case '':
                    break;
                default:
                    throw new ConfigurationLoaderException("Type de paramètre '$type' inconnu dans '$input'");
            }

            if ($required && $value === "") {
                throw new ConfigurationLoaderException("Valeur '$name' requise");
            }
            return $value;
        } else {
            return $value;
        }
    }
}
