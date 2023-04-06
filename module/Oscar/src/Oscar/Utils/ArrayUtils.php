<?php


namespace Oscar\Utils;

/**
 * Class ArrayUtils
 * @package Oscar\Utils
 */
class ArrayUtils
{
    public static function explodeFromString( ?string $from, string $separator, bool $throw = true ) :array
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
}