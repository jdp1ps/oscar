<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-29 15:53
 * @copyright Certic (c) 2017
 */

namespace Oscar;


class OscarVersion
{
    const MAJOR = 2;
    const MINOR = 14;
    const PATCH = 0;
    const NAME = "Starling";

    public static function getBuild(){
        return sprintf('v%s.%s.%s "%s"', self::MAJOR, self::MINOR, self::PATCH, self::NAME);
    }
}