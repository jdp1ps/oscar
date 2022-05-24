<?php


namespace Oscar\Factory;


use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;

class ActivityGantJson
{
    private $toDayMultiplier = 60*60*24;

    public function formatOne( Activity $activity ) :array
    {
        $out = [
            'id' => $activity->getId(),
            'acronym' => $activity->getAcronym(),
            'label' => $activity->getLabel(),
            'milestones' => [],
            'start' => $activity->getDateStartStr(),
            'end' => $activity->getDateEndStr(),
            'start_time' => $activity->getDateStart() ? $this->normalizeTime($activity->getDateStart()->getTimestamp()) : null ,
            'end_time' => $activity->getDateEnd() ? $this->normalizeTime($activity->getDateEnd()->getTimestamp()) : null ,
            'type' => $activity->getActivityType() ? $activity->getActivityType()->getLabel() : ""
        ];

        /** @var ActivityDate $milestone */
        foreach ($activity->getMilestones() as $milestone) {
            $out['milestones'][] = [
              'id' => $milestone->getId(),
              'label' => $milestone->getType()->getLabel(),
              'date' => $milestone->getDateStartStr(),
              'date_time' => $this->normalizeTime($milestone->getDateStart()->getTimestamp()),
            ];
        }

        return $out;
    }

    public function formatAll( array $activities ) :array
    {
        $out = [
            'items' => []
        ];

        $minDate = $this->normalizeTime(time());
        $maxDate = $this->normalizeTime(time());
        $minDateStr = $this->normalizeTime(date("Y-m-d"));
        $maxDateStr = $this->normalizeTime(date("Y-m-d"));

        /** @var Activity $activity */
        foreach ($activities as $activity) {

            $activityStart = null;
            $activityStartTime = null;
            $activityEnd = null;
            $activityEndTime = null;

            if( $activity->getDateStart() ){
                $activityStart = $activity->getDateStartStr();
                $activityStartTime = $this->normalizeTime($activity->getDateStart()->getTimestamp());
                if( $minDate > $activityStartTime ){
                    $minDate = $activityStartTime;
                    $minDateStr = $activityStart;
                }
            }

            if( $activity->getDateEnd() ){
                $activityEnd = $activity->getDateEndStr();
                $activityEndTime = $this->normalizeTime($activity->getDateEnd()->getTimestamp());
                if( $maxDate < $activityEndTime ){
                    $maxDate = $activityEndTime;
                    $maxDateStr = $activityEnd;
                }
            }



            $out['items'][] = $this->formatOne($activity);
        }
        $out['min_time'] = $minDate;
        $out['max_time'] = $maxDate;
        $out['min_date_str'] = $minDateStr;
        $out['max_date_str'] = $maxDateStr;
        return $out;
    }

    private function normalizeTime( int $time ) :int
    {
        return intval($time / $this->toDayMultiplier);
    }
}