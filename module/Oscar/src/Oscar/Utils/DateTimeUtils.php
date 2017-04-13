<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 05/08/15 10:51
 * @copyright Certic (c) 2015
 */

namespace Oscar\Utils;


class DateTimeUtils {
    /**
     * Retourne la date au format $format, si la date est NULL, retourne une
     * chaîne vide.
     *
     * @param \DateTime $datetime
     * @param string $format
     * @return string
     */
    public static function toStr( \DateTime $datetime = null, $format = 'Y-m-d H:i:s') {
        return $datetime ? $datetime->format($format) : '';
    }

    public static function toDatetime( $value )
    {
        if( $value == null ){
            return null;
        } else {
            return new \DateTime($value);
        }
    }
}