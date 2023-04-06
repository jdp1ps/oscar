<?php

namespace Oscar\Utils;

class TypeUtils
{
    public static function getIntegerFromString(?string $from, bool $throw = true): ?int
    {
        set_error_handler(
            function ($errno, $errstr, $errfile, $errline) {
                throw new OscarTypeException("Non integer string");
            }
        );
        $temp = 1 / ($from + ($from === "0"));
        $int = (int)$from;
        restore_error_handler();
        return $int;
    }
}