<?php

namespace Oscar\Utils;

use Oscar\Exception\OscarException;

class TypeUtils
{
    public static function getIntegerFromString(?string $from, bool $throw = true): ?int
    {
        if($from == "0"){
            return 0;
        } else {
            $inted = intval($from);
            if( $inted !== 0 ){
                return $inted;
            } else {
                throw new OscarTypeException("Impossible de convertir la chaîne '$from' en entier");
            }
        }
    }
}