<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 21/08/18
 * Time: 10:57
 */

namespace Oscar\Formatter;
use Oscar\Entity\TimeSheet;

/**
 * Mise en forme des données pour un affichage récapitulatif mensuel.
 *
 * Class TimesheetsMonthFormatter
 * @package Oscar\Formatter
 */
class TimesheetsMonthFormatter
{


    private $_cacheTotalDaysMonth = [];

    public function getMonthDaysLength($month, $year){
        $key = sprintf('%s-%s', $year, $month);
        if( !array_key_exists($key, $this->_cacheTotalDaysMonth) ){
            $dateRef = new \DateTime($key.'-01');
            $this->_cacheTotalDaysMonth[$key] = cal_days_in_month(CAL_GREGORIAN, (int)$dateRef->format('m'), (int)$dateRef->format(('Y')));
        }
        return $this->_cacheTotalDaysMonth[$key];
    }

    /**
     * @return \IntlDateFormatter
     */
    public function getDateFormatter(){
        static $formatter;
        if( $formatter == null ){
            $formatter = new \IntlDateFormatter(
                'fr_FR',
                \IntlDateFormatter::FULL,
                \IntlDateFormatter::FULL,
                'Europe/Paris',
                \IntlDateFormatter::GREGORIAN,
                'd MMMM YYYY');
        }
        return $formatter;
    }

    /**
     * Produit un tableau de données formatté pour l'affichage JSON depuis une liste de timesheet.
     *
     * @param $timesheets
     * @param $month
     * @param $year
     */
    public function format( $timesheets, $month, $year, $wps = null ){

        $totalDays = $this->getMonthDaysLength($month, $year);
        $formatter = $this->getDateFormatter();
        $formatter->setPattern('MMMM YYYY');
        $firstDay = new \DateTime(sprintf('%s-%s-01', $year, $month));


        $output = [
            'totaltimesheets' => count($timesheets),
            'month' => $month,
            'year' => $year,
            'monthLabel' => $formatter->format($firstDay),
            'total' => 0.0,
            'totalDays' => $totalDays,
            'totalWps' => $wps
        ];


        $formatter->setPattern('eeee d');

        $days = [];



        /** @var TimeSheet $timesheet */
        foreach ($timesheets as $timesheet){
            $day = (integer)$timesheet->getDateFrom()->format('d');

            if( !array_key_exists($day, $days) ) {
                $days[$day] = [
                    "label" => "$day",
                    "daylabel" => "$day",
                    'dayname' => $formatter->format($timesheet->getDateFrom()),
                    "total" => 0.0,
                    "details" => [],
                    //'timesheets' => []
                ];
            }

            if( $wps != null ){

                $pack = $timesheet->getWorkpackage()->getCode();
                if( !array_key_exists($pack, $days[$day]['details']) ){
                    foreach ($wps as $wp=>$total ) {
                        $days[$day]['details'][$wp] = 0.0;
                    }
                }
                $days[$day]['details'][$pack] += $timesheet->getDuration();
                $output['totalWps'][$pack] += $timesheet->getDuration();

            }

            $days[$day]['total'] += $timesheet->getDuration();
            $output['total'] += $timesheet->getDuration();
           // $days[$day]['timesheets'][] = $timesheet->toJson2();

        }

        $output['days'] = $days;

        return $output;
    }

}