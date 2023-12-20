<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 05/08/15 10:51
 * @copyright Certic (c) 2015
 */

namespace Oscar\Utils;


use Oscar\Exception\OscarException;

class DateTimeUtils
{
    /**
     * Retourne la date au format $format, si la date est NULL, retourne une
     * chaîne vide.
     *
     * @param \DateTime $datetime
     * @param string $format
     * @return string
     */
    public static function toStr(\DateTime $datetime = null, $format = 'Y-m-d H:i:s')
    {
        return $datetime ? $datetime->format($format) : '';
    }

    public static function periodInside($periodStr, \DateTime $from, \DateTime $to)
    {
        $start = new \DateTime($from->format(\DateTime::W3C));
        $start->setTime(0, 0, 0);
        $startInt = $start->getTimestamp();

        $end = new \DateTime($to->format(\DateTime::W3C));
        $end->setTime(23, 59, 59);
        $endInt = $end->getTimestamp();

        $period = new \DateTime($periodStr . '-15 12:00:00');
        $periodInt = $period->getTimestamp();

        $test = $periodInt >= $startInt && $periodInt <= $endInt;

        //echo "\n$startInt <= $periodInt <= $endInt : " . ($test?'TRUE':'FALSE');

        return $test;
    }

    public static function humanDate(\DateTime $date, string $format = '')
    {
        $fmt = datefmt_create(
            'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            'Europe/Paris',
            \IntlDateFormatter::GREGORIAN,
            $format
        );

        return datefmt_format($fmt, $date->getTimestamp());
    }

    public static function normalizePeriodStr( string $period ) :string
    {
        $re = '/([0-9]{1,2})-([0-9]{4})/m';
        $format = '%s-%s';
        $error = sprintf("La période '%s' est invalide", $period);

        if( preg_match_all($re, $period, $matches, PREG_SET_ORDER, 0) ){
            $month = intval($matches[0][1]);
            if( $month > 12 || $month < 1 ){
                throw new \Exception($error);
            }
            if( $month < 10 ){
                $month = '0'.$month;
            }
            return sprintf($format, $month, $matches[0][2]);
        }

        else {
            throw new OscarException($error);
        }
    }

    public static function periodBounds($period, $daysDetails = false)
    {
        $dateRef = new \DateTime(sprintf('%s-01', $period));
        $nbr = cal_days_in_month(CAL_GREGORIAN, (int)$dateRef->format('m'), (int)$dateRef->format(('Y')));
        $dateFin = $dateRef->format('Y-m-' . $nbr);
        $startLabel = self::humanDate($dateRef);
        $endLabel = self::humanDate((new \DateTime($dateFin)));
        $periodLabel = self::humanDate($dateRef, 'MMM yyyy');

        $datas = [
            'totalDays' => $nbr,
            'year' => $dateRef->format('Y'),
            'month' => $dateRef->format('m'),
            'periodLabel' => $periodLabel,
            'start' => $dateRef->format('Y-m-01 00:00:00'),
            'startLabel' => $startLabel,
            'firstDay' => $dateRef->format('Y-m-01'),
            'end' => $dateRef->format('Y-m-' . $nbr . ' 23:59:59'),
            'endLabel' => $endLabel,
            'lastDay' => $dateRef->format('Y-m-' . $nbr),
        ];

        $daysLabel = ['', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        $i = 1;
        $days = [];

        for ($i = 1; $i < $nbr; $i++) {
            $forDay = new \DateTime($period . '-' . $i);
            $days[$i] = $daysLabel[$forDay->format('N')];
        }

        $datas['days'] = $days;

        return $datas;
    }

    public static function getPeriodStrFromDateStr($dateStr)
    {
        $date = new \DateTime($dateStr);
        return $date->format('Y-m');
    }

    /**
     * Retourne la liste des périodes entre 2 périodes.
     *
     * @param $from
     * @param $to
     * @return array
     */
    public static function allperiodsBetweenTwo($from, $to)
    {
        if (is_object($from) && get_class($from) == \DateTime::class) {
            $from = $from->format('Y-m');
        }
        if (is_object($to) && get_class($to) == \DateTime::class) {
            $to = $to->format('Y-m');
        }

        $start = new \DateTime($from . '-01');
        $end = new \DateTime($to . '-01');

        $startYear = (int)$start->format('Y');
        $startMonth = (int)$start->format('m');

        $endYear = (int)$end->format('Y');
        $endMonth = (int)$end->format('m');

        $periods = [];

        while (!($startYear == $endYear && $startMonth == $endMonth)) {
            $periods[] = self::getCodePeriod($startYear, $startMonth);
            $startMonth++;
            if ($startMonth > 12) {
                $startYear++;
                $startMonth = 1;
            }
        }
        $periods[] = self::getCodePeriod($startYear, $startMonth);

        return $periods;
    }

    /**
     * Retourne un tableau contenant la liste des périodes retenues entre une liste de date.
     *
     * @param mixed ...$bounds
     * @return array
     */
    static public function allPeriodsFromDates(...$bounds)
    {
        $out = [];
        foreach ($bounds as $bound) {
            $periodStart = DateTimeUtils::getPeriodStrFromDateStr($bound[0]);
            $periodEnd = DateTimeUtils::getPeriodStrFromDateStr($bound[1]);
            $periodsBound = self::allperiodsBetweenTwo($periodStart, $periodEnd);
            $out = array_merge($out, $periodsBound);
        }
        $out = array_unique($out);
        sort($out);
        return $out;
    }

    public static function toDatetime($value)
    {
        if ($value == null /*|| $value == 'null'*/) {
            return null;
        } else {
            try {
                return new \DateTime($value);
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    public static function getCodePeriod($year, $month)
    {
        $year = (int)$year;
        $month = (int)$month;
        if ($month < 1 || $month > 12) {
            throw new \Exception(_("Mois invalide"));
        }
        return sprintf('%s-%s', $year, ($month < 10 ? '0' . $month : $month));
    }

    public static function extractPeriodDatasFromString($str)
    {
        $re = '/([0-9]{4})\-(10|11|12|0?[1-9])$/';
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        if ($matches) {
            $month = intval($matches[0][2]);
            $year = intval($matches[0][1]);

            $fmt = datefmt_create(
                'fr_FR',
                \IntlDateFormatter::FULL,
                \IntlDateFormatter::NONE,
                'Europe/Paris',
                \IntlDateFormatter::GREGORIAN,
                'MMMM yyyy'
            );


            $dateRef = new \DateTime(sprintf('%s-%s-01', $year, $month));
            $periodLabel = $fmt->format($dateRef);

            return [
                'period' => sprintf('%s-%s', $year, $month),
                'periodLabel' => $periodLabel,
                'periodCode' => sprintf('%s-%s', $year, ($month < 10 ? '0' . $month : $month)),
                'month' => $month,
                'year' => $year,
            ];
        }
        throw new OscarException(sprintf("Format de période '%s' incorrect", $str));
    }
}