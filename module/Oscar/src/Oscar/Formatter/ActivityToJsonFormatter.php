<?php


namespace Oscar\Formatter;


use Oscar\Entity\Activity;

class ActivityToJsonFormatter
{
    public function format(Activity $activity) : array {
        $output = $activity->toArray(true);
        return $output;
    }
}