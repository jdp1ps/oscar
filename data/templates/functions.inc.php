<?php

require_once __DIR__ . '/variables.inc.php';

/**
 * @param $duration
 * @return string
 */
function duration ($duration): string {
    $duration = (float)$duration;
    $heures = floor($duration);
    $minutes = round(($duration - $heures) * 60);
    if ($minutes < 10) {
        $minutes = '0' . $minutes;
    }
    return sprintf('%s:%s', $heures, $minutes);
};

/**
 * @param $duration
 * @return string
 */
function durationRounded ($duration): string {
    $duration = (float)$duration;
    $roundStep = 5;
    $heures = floor($duration);
    $minutes = round(($duration - $heures) * 60 / $roundStep) * $roundStep;
    if ($minutes < 10) {
        $minutes = '0' . $minutes;
    }
    return sprintf('%s:%s', $heures, $minutes);
};

/**
 * @param $str
 * @return string
 * @throws Exception
 */
function renderDate ($str): string {
    $date = new DateTime($str);
    return $date->format("F Y m d");
}
?>