<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 05/08/15 10:51
 * @copyright Certic (c) 2015
 */

namespace Oscar\Utils;


use Oscar\Exception\OscarException;

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

    public static function periodBounds( $period ){
        $dateRef = new \DateTime(sprintf('%s-01', $period));
        $nbr = cal_days_in_month(CAL_GREGORIAN, (int)$dateRef->format('m'), (int)$dateRef->format(('Y')));
        return [
            'totalDays' => $nbr,
            'year' => $dateRef->format('Y'),
            'month' => $dateRef->format('m'),
            'start' => $dateRef->format('Y-m-01 00:00:00'),
            'end' => $dateRef->format('Y-m-' . $nbr .' 23:59:59'),
        ];
    }

    public static function toDatetime( $value )
    {
        if( $value == null ){
            return null;
        } else {
            return new \DateTime($value);
        }
    }

    public static function extractPeriodDatasFromString($str){
        $re = '/([0-9]{4})\-(10|11|12|0?[1-9])$/';
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        if( $matches ){
            $month = intval($matches[0][2]);
            $year = intval($matches[0][1]);
            return [
                'period' => sprintf('%s-%s', $year, $month),
                'month' => $month,
                'year' => $year,
            ];
        }
        throw new OscarException(sprintf("Format de période '%s' incorrect", $str));
    }
}