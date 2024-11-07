<?php

require_once __DIR__ . '/variables.inc.php';
$custom = __DIR__ . '/custom.inc.php';
if( file_exists($custom) ){
    require_once $custom;
}
/**
 * @param $duration
 * @return string
 */
function duration ($duration): string {
    $duration = floatval($duration);
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
    $duration = floatval($duration);
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