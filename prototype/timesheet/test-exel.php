<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-12-15 18:05
 * @copyright Certic (c) 2017
 */


require __DIR__.'/../../vendor/autoload.php';


function getTimeSheet($year, $month, $day, $start, $end, $person, $wp, $description=""){
    $day = $year.'-'.$month.'-'.$day;

    $dateStart = new DateTime($day.' '.$start);
    $dateEnd = new DateTime($day.' '.$end);

    $timsheet = new \Oscar\Entity\TimeSheet();
    $timsheet->setDateFrom($dateStart)
        ->setPerson($person)
        ->setDateTo($dateEnd)
        ->setWorkpackage($wp);

    return $timsheet;
}

$staff = [
    [ "firstname" => "Pablo", "lastname" => "CUBIDES", "file" => "Cubides.csv" ],
    [ "firstname" => "Vlerë", "lastname" => "MEHMETI", "file" => "Mehmeti.csv" ],
    [ "firstname" => "Jérôme", "lastname" => "POINEAU", "file" => "Poineau.csv" ],
    [ "firstname" => "Daniele", "lastname" => "TURCHETTI", "file" => "Turchetti.csv" ],
];

$moisStr = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'aout', 'septembre', 'octobre', 'novembre', 'décembre'];

$timesheets = [];

$project = new \Oscar\Entity\Project();
$project->setAcronym('TOSSIBERG');

$activity = new \Oscar\Entity\Activity();
$activity->setLabel('H2020 Projet Tossiberg')

    ->setDateStart(new DateTime("2015-07-01 00:00"))
    ->setDateEnd(new DateTime("2020-06-30 23:59"))
    ->setCodeEOTP("909CB176")
    ->setProject($project);

$w = new \Oscar\Entity\WorkPackage();
$w->setCode('HWP')
    ->setLabel('Hors WP')
    ->setActivity($activity);



foreach ($staff as $data){
    $person = new \Oscar\Entity\Person();
    $person->setFirstname($data['firstname'])
        ->setLastname($data['lastname']);

    $structuredDatas = [];

    $handler = fopen($data['file'], 'r');
    fgetcsv($handler, 1000);

    while(($line = fgetcsv($handler, filesize($data['file'])))){


        $year = $line[0];
        $month = $line[1];
        $day = $line[2];
        $hourStart = $line[3];
        $hourEnd = $line[4];
        $period = $moisStr[$month] . ' ' . $year;
        $wp = $w->getCode();


        $dateStart = sprintf("%s-%s-%s %s",
            $line[0], $line[1], $line[2], $line[3]);

        $dateEnd = sprintf("%s-%s-%s %s",
            $line[0], $line[1], $line[2], $line[4]);

        $timesheet = getTimeSheet($line[0], $line[1], $line[2], $line[3], $line[4], $person, $w);
        $timesheet->setStatus(\Oscar\Entity\TimeSheet::STATUS_ACTIVE);


        if( !array_key_exists($period, $structuredDatas) ){
            $structuredDatas[$period] = [];
        }


        if( !array_key_exists($wp, $structuredDatas[$period]) ){
            $structuredDatas[$period][$wp] = [];
        }

        if( !array_key_exists($day,  $structuredDatas[$period][$wp]) ){
            $structuredDatas[$period][$wp][$day] = [];
        }

        $structuredDatas[$period][$wp][$day][] = $timesheet;
    }

    $timesheets[$data['lastname']] =
        [
            'person' => $person,
            'activity' => $activity,
            'timesheets' => $structuredDatas
        ];
}


    $cellDays = ['C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U', 'V', 'W','X','Y','Z','AA', 'AB', 'AC', 'AD', 'AG'];
    $lineWpFormula = '=SUM(C%s:AG%s)';

foreach( $timesheets as $lastName=>$timesheetPerson ){

    /** @var \Oscar\Entity\Person $person */
    $person = $timesheetPerson['person'];

    /** @var \Oscar\Entity\Activity $activity */
    $activity = $timesheetPerson['activity'];



    foreach( $timesheetPerson['timesheets'] as $period=>$timesheetsPeriod ){
        $lineWpStart = 10;
        $lineWpCurent = $lineWpStart;
        $lineWpCount = 0;
        $spreadsheet = PHPExcel_IOFactory::load('ods-base-test.xls');


        $spreadsheet->getActiveSheet()->setCellValue('A1', $activity->getLabel());
        $spreadsheet->getActiveSheet()->setCellValue('C3', (string)$person);
        $spreadsheet->getActiveSheet()->setCellValue('C4', 'Université de Caen');
        $spreadsheet->getActiveSheet()->setCellValue('C5', $activity->getAcronym());

        $spreadsheet->getActiveSheet()->setCellValue('U3', $activity->getDateStart()->format("d F Y"));
        $spreadsheet->getActiveSheet()->setCellValue('U4', $activity->getDateEnd()->format("d F Y"));
        $spreadsheet->getActiveSheet()->setCellValue('U5', '2015DRI00394');
        $spreadsheet->getActiveSheet()->setCellValue('U6', $activity->getCodeEOTP());

        $spreadsheet->getActiveSheet()->setCellValue('C6', $period);
        $spreadsheet->getActiveSheet()->setCellValue('B8', $period);
        $spreadsheet->getActiveSheet()->setCellValue('A9', "UE - " . $project->getAcronym());

        foreach ($timesheetsPeriod as $workpackage=>$timesheetsWorkpackage) {

            $rowNum = $lineWpStart + $lineWpCount;
            $spreadsheet->getActiveSheet()->insertNewRowBefore(($rowNum + 1));
            for( $i=0; $i<count($cellDays); $i++ ){
                $day = $i+1;
                $cellIndex = $cellDays[$i].$rowNum;
                $totalDay = 0.0;
                if( array_key_exists($day, $timesheetsWorkpackage) ){
                    foreach ($timesheetsWorkpackage[$day] as $timesheet ){
                        $totalDay += $timesheet->getDuration();
                    }
                }
                $spreadsheet->getActiveSheet()->setCellValue($cellIndex, $totalDay);
            }
            $spreadsheet->getActiveSheet()->setCellValue('A'.$rowNum, "");
            $spreadsheet->getActiveSheet()->setCellValue('B'.$rowNum, $workpackage);
            $spreadsheet->getActiveSheet()->setCellValue('AH'.$rowNum, sprintf($lineWpFormula, $rowNum, $rowNum));
            $lineWpCount++;
        }

        $rowNum = $lineWpStart + $lineWpCount + 1;

        for( $i=0; $i<count($cellDays); $i++ ){
            $day = $i+1;
            $cellIndex = $cellDays[$i].$rowNum;
            $sum = "=SUM(" . $cellDays[$i] .'10:' .$cellDays[$i].($rowNum-1) .')';

            $spreadsheet->getActiveSheet()->setCellValue($cellIndex, $sum);
        }

        $edited = PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel5');

        $edited->save($lastName." " . $period . ".xls");

    }
}


/*




// Ligne de départ









insertWorkpackageRow(0, $spreadsheet, null);
insertWorkpackageRow(0, $spreadsheet, null);
insertWorkpackageRow(0, $spreadsheet, null);

//var_dump($spreadsheet->getActiveSheet()->getCell('AH10')->getStyle());

$edited = PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel5');

$edited->save("output.xls");
*/