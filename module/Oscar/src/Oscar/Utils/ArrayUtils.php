<?php


namespace Oscar\Utils;

/**
 * Class ArrayUtils
 * @package Oscar\Utils
 */
class ArrayUtils
{
    public static function implode(string $separator, ?array $array = null) :string
    {
        if( $array === null ) {
            return "";
        }
        return implode($separator, $array);
    }


    public static function explodeFromString( ?string $from = null, string $separator = ',', bool $throw = true ) :array
    {
        if( $from == null ){
            return [];
        }

        if( !is_string($from) ){
            if( $throw ){
                throw new \Exception("Impossible de convertir la chaîne");
            }
            return [];
        }

        return explode($separator, $from);
    }

    public static function explodeIntegerFromString( ?string $from, string $separator = ",", bool $throw = true ) :array
    {
        return array_map(
            function ($item) { return TypeUtils::getIntegerFromString($item); },
            self::explodeFromString($from, $separator, $throw)
        );
    }

    public static function normalizeArray( array $array, bool $removeZero = false ){
        $out = [];
        // todo Supprimer les '0' à gauche
        foreach ($array as $entry) {
            if( $entry == '0' || $entry == 0 ){
                if( !$removeZero ) {
                    $out[] = 0;
                }
            } else {
                $int = intval($entry);
                if( $int ){
                    $out[] = $int;
                }
            }
        }
        return $out;
    }
}