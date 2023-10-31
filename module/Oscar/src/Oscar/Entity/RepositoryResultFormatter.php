<?php

namespace Oscar\Entity;

use Oscar\Exception\OscarException;
use Oscar\Formatter\OscarFormatterConst;

class RepositoryResultFormatter
{
    private array $formats = [];

    /**
     * @param string $format Clef pour le format
     * @param string $method Méthode (get(): string) à utiliser pour obtenir la clef
     * @return void
     */
    public function addFormat(string $format, string $method): void
    {
        $this->formats[$format] = $method;
    }

    /**
     * Organisation des données.
     * - FORMAT_ARRAY_OBJECT : [Object,Object,...]
     * - FORMAT_ARRAY_ID_OBJECT : [ID => Object, ID => Object,...]
     * - FORMAT_ARRAY_FLAT : [toString(), toString(),...]
     *
     * @param array $arrayOfObject
     * @param string $format
     * @return array
     * @throws OscarException
     */
    public function output(array $arrayOfObject, string $format = OscarFormatterConst::FORMAT_ARRAY_OBJECT): array
    {
        if ($format === OscarFormatterConst::FORMAT_ARRAY_OBJECT) {
            return $arrayOfObject;
        }

        $output = [];

        foreach ($arrayOfObject as $object) {
            switch ($format) {
                case OscarFormatterConst::FORMAT_ARRAY_ID_OBJECT:
                    $output[$object->getId()] = $object;
                    break;
                case OscarFormatterConst::FORMAT_ARRAY_FLAT:
                    $output[] = (string)$object;
                    break;
                case OscarFormatterConst::FORMAT_ARRAY_ID_VALUE:
                    $output[$object->getId()] = (string)$object;
                    break;
                default:
                    if (!array_key_exists($format, $this->formats)) {
                        throw new OscarException("Format '$format' de données tabulaire inconnu");
                    }
                    $method = $this->formats[$format];
                    $key = $object->$method();
                    $output[$key] = $object;
                    break;
            }
        }

        return $output;
    }
}