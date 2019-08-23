<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 21/08/19
 * Time: 16:51
 */

namespace Oscar\Formatter;


use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class TimesheetPersonPeriodFormatter
{

    public function format($datas, $options=null){
        echo "<pre>";
        var_dump($datas);
        die("TODO");
    }

    private $currentLineIndex;
    private $currentColIndex;
    private $letters = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
    private $spreadsheet;
    private $jumpCol = 0;
    private $styles;

    private $width = 0;
    private $height = 0;


    public function __construct()
    {
        $this->currentColIndex = 0;
        $this->currentLineIndex = 1;
        $this->spreadsheet = new Spreadsheet();
        $this->styles = [];
    }

    public function addStyle($name, $options){
        $this->styles[$name] = $options;
    }

    /**
     * Déplace le curseur d'écriture à la colonne suivante
     * @return $this
     */
    public function nextCol(){
        $this->currentColIndex += $this->jumpCol+1;
        $this->jumpCol = 0;
        $this->width = max($this->width, $this->currentColIndex+1);
        return $this;
    }

    public function autoSizeColumns(){
        for($i=0; $i<$this->width; $i++){
            $col = $this->getColStr($i);
            $this->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * @return $this
     */
    public function nextLine(){
        $this->currentLineIndex++;
        $this->currentColIndex = 0;
        return $this;
    }

    /**
     * @param $letter
     * @return $this
     */
    public function setCol($letter){
        $this->currentColIndex = array_search($letter, $this->letters);
        return $this;
    }

    public function getCurrentLine(){
        return $this->currentLineIndex;
    }

    /**
     * @return string
     */
    public function getCurrentCol(){
        return $this->getColStr($this->currentColIndex);
    }

    /**
     * @param $index
     * @return string
     */
    private function getColStr($index){
        $out = "";
        $a = floor($index / count($this->letters));
        if( $a > 0 ){
            $out .= $this->letters[$a-1];
        }
        $b = $index % count($this->letters);
        $out .= $this->letters[$b];
        return "$out";
    }

    /**
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getActiveSheet(){
        return $this->spreadsheet->getActiveSheet();
    }


    /**
     * @return string
     */
    public function getCurrentCellPosition(){
        return $this->getCurrentCol()."".$this->currentLineIndex;
    }

    /**
     * @return \PhpOffice\PhpSpreadsheet\Style\Style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getCurrentStyle(){
        return $this->getActiveSheet()->getStyle($this->getCurrentCellPosition());
    }

    /**
     * @param $content
     * @param int $colspan
     * @param bool $nextCol
     * @param null $style
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function drawCell($content, $colspan=0, $nextCol=true, $style=null) {

        // Styles
        if( $style != null ){
            if( !array_key_exists($style, $this->styles) ){
                throw new \Exception("Style '$style' non référencé'");
            }

            $this->getActiveSheet()->getStyle($this->getCurrentCellPosition())->applyFromArray($this->styles[$style]);
        }

        $this->getActiveSheet()->setCellValue($this->getCurrentCellPosition(), $content);

        if( $colspan > 0 ){
            $colA = $this->getCurrentCol();
            $colB = $this->getColStr($this->currentColIndex + $colspan);
            $line = $this->getCurrentLine();
            $merge = sprintf('%s%s:%s%s', $colA, $line, $colB, $line);
            $this->getActiveSheet()->mergeCells($merge);
            $this->jumpCol = $colspan;
        }

        if( $nextCol ){
            $this->nextCol();
        }
        return $this;
    }

    /**
     * @param $path
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function generate($filename){
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->spreadsheet);
        $writer->save('php://output');
        die();
    }

    public function generatePdf($filename){
        //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        // TODO orientation paysage avec DOMPdf
        $writer = IOFactory::createWriter($this->spreadsheet, 'Dompdf');
        // new Mpdf($this->spreadsheet);
        $writer->save('php://output');
        die();
    }



    public function output($datas, $outputFormat='excel'){

        $filename = $datas['activity']['numOscar'].'_'.$datas['period']['year'].'-'.$datas['period']['month'];
        ///////// LA classe à Dalas
        $entete = [
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => 'FF537992',
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'ffdde6eb',
                ],
                'endColor' => [
                    'argb' => 'ffdcedf9',
                ],
            ],
        ];
        $this->addStyle("entete", $entete);

        $labelTitle = [
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => "FF555555",
                ],
                'size' => 10
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ffdde6eb" ]],
        ];
        $this->addStyle("labelTitle", $labelTitle);

        $labelValue = [
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => "FF000000",
                ],
                'size' => 10
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ffffffff" ]],
        ];
        $this->addStyle("labelValue", $labelValue);

        $total = [
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => 'FF537992',
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ];

        $this->addStyle("total", $total);


        $colorResearch      = '71bdae'; $colorResearchBG    = 'ebf8f5';
        $colorEducation     = 'c2e0ae'; $colorEducationBG   = 'ecf6e5';
        $colorAbs           = 'f8aa4a'; $colorAbsBG         = 'faefea';
        $colorOther         = 'd1d6a5'; $colorOtherBG       = 'f8faea';

        $baseFontSize = 10;

        $headDayEven = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ff71BDAE" ]],];

        $this->addStyle("headDayEven", $headDayEven);

        $headDayOdd = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ffA3DCCF" ]],];

        $headDayLock = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ffdae5e2" ]],];


        $cellEmpty = [ 'font' => [ 'normal' => true, 'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            ];
        $this->addStyle("cellEmpty", $cellEmpty);

        $cellLock = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ffdae5e2" ]],
        ];
        $this->addStyle("cellLock", $cellLock);

        $cellValued = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ffdae5e2" ]],
            ];
        $this->addStyle("cellValued", $cellValued);

        $headGroup = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ffdae5e2" ]],
            ];
        $this->addStyle("headGroup", $headGroup);

        $headSubGroup = [ 'font' => [ 'normal' => true, 'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ffA3DCCF" ]],
        ];
        $this->addStyle("headSubGroup", $headSubGroup);


        $this->addStyle("headDayOdd", $headDayOdd);
        $this->addStyle("headResearch", $headDayOdd);
        $this->addStyle("headDay", $headDayOdd);
        $this->addStyle("headDayLock", $headDayLock);




        $headAbs = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ff$colorAbs" ]],];
        $this->addStyle("headAbs", $headAbs);

        $headEducation = [ 'font' => [ 'bold' => true,'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ff$colorEducation"  ]],];
        $this->addStyle("headEducation", $headEducation);

        $headOther = [ 'font' => [ 'bold' => true,'size' => $baseFontSize],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ff$colorOther" ]],];
        $this->addStyle("headOther", $headOther);

        $withValue = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize-1 ],
            'borders' => [
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'left' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
            ],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
        ];
        $this->addStyle("withValue", $withValue);



        $cellTotalBottom = [ 'font' => [ 'bold' => true, 'size' => $baseFontSize+1 ],
            'borders' => [
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ff000000'] ]
            ],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
        ];
        $this->addStyle("cellTotalBottom", $cellTotalBottom);

        $noValue = [ 'font' => [ 'bold' => false, 'size' => $baseFontSize-1, 'color' => [ 'argb' => 'ff808080' ]],
            'borders' => [
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'left' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
            ],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]];
        $this->addStyle("noValue", $noValue);


        $comment = [ 'font' => [ 'bold' => false, 'size' => $baseFontSize-1, 'color' => [ 'argb' => 'ff333333' ]],
            'borders' => [
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'left' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
            ],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,]];
        $this->addStyle("comment", $comment);

        $personComment = [ 'font' => [ 'bold' => false, 'size' => $baseFontSize-1, 'color' => [ 'argb' => 'ff555555' ]],
            'borders' => [
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'left' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
            ],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,]];
        $this->addStyle("personComment", $personComment);

        $person = [
            'font' => [
                'bold' => true,
                'size' => $baseFontSize,
                'color' => [
                    'argb' => 'FF333333',
                ],
            ],
            'borders' => [
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'left' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => 'fffefefe' ]
            ],

        ];
        $this->addStyle("person", $person);


        $totalColumn = [
            'font' => [
                'bold' => true,
                'size' => $baseFontSize,
            ],
            'borders' => [
                'left' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ff000000'] ],
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'ffd7dbce'] ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [ 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => "ffffffff" ]],
        ];
        $this->addStyle("totalColumn", $totalColumn);


        $daysWidth            = count($datas['daysInfos']);
        $ceWidth            = 0;
        $educationWidth     = 0;
        $absWidth           = 0;
        $otherWidth         = 0;
        $researchWidth      = 0;

        $fullWidth = 4 + $daysWidth + 3;


        $sizing = floor(($fullWidth -4) / 4);


        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(40);
        $this->drawCell("FEUILLE de TEMPS de " . $datas['person'], $fullWidth, true, 'entete');
        $this->nextLine();
        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(30);
        $this->drawCell($datas['activity']['label'], $fullWidth, true, 'entete');
        $this->nextLine();
        $this->nextLine();


        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(4);
        $this->drawCell("", $fullWidth, true, 'labelTitle');
        $this->nextLine();

        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(20);
        $this->drawCell("", 0, true, 'labelTitle');
        $this->drawCell("Début : ", $sizing, true, 'labelTitle');
        $this->drawCell($datas['period']['startLabel'], $sizing, true, 'labelValue');
        $this->drawCell("", 0, true, 'labelTitle');
        $this->drawCell("Fin : ", $sizing, true, 'labelTitle');
        $this->drawCell($datas['period']['endLabel'], $sizing, true, 'labelValue');
        $this->drawCell("", 0, true, 'labelTitle');
        $this->nextLine();


        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(4);
        $this->drawCell("", $fullWidth, true, 'labelTitle');
        $this->nextLine();

        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(20);
        $this->drawCell("", 0, true, 'labelTitle');
        $this->drawCell("PFI : ", $sizing, true, 'labelTitle');
        $this->drawCell($datas['activity']['PFI'], $sizing, true, 'labelValue');
        $this->drawCell("", 0, true, 'labelTitle');
        $this->drawCell(" : ", $sizing, true, 'labelTitle');
        $this->drawCell("", $sizing, true, 'labelValue');
        $this->drawCell("", 0, true, 'labelTitle');
        $this->nextLine();

        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(4);
        $this->drawCell("", $fullWidth, true, 'labelTitle');
        $this->nextLine();

        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(20);
        $this->drawCell("", 0, true, 'labelTitle');
        $this->drawCell("Acronyme : ", $sizing, true, 'labelTitle');
        $this->drawCell($datas['activity']['projectacronym'], $sizing, true, 'labelValue');
        $this->drawCell("", 0, true, 'labelTitle');
        $this->drawCell("N°OSCAR : ", $sizing, true, 'labelTitle');
        $this->drawCell($datas['activity']['numOscar'], $sizing, true, 'labelValue');
        $this->drawCell("", 0, true, 'labelTitle');
        $this->nextLine();

        $this->drawCell("", $fullWidth, true, 'labelTitle');
        $this->nextLine();


        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(30);
        $this->drawCell(" ", 0, true);


// LOTS
        $this->drawCell($datas['period'], 7, true, 'headResearch');

        $odd = true;
        foreach ($datas['daysInfos'] as $i=>$day) {

            $this->drawCell(/*$day['label'] . */"$i", 0, true, $day['locked'] ? 'headDayLock' : ($odd ? 'headDayOdd' : 'headDayEven'));
            $odd = !$odd;
        }
        $this->drawCell('Total', 0, true, 'headResearch');

        $this->nextLine();


        foreach ($datas['declarations']['activities'] as $labelActivity=>$dataActivity) {
            $this->drawCell('', 0, true);
            $this->drawCell($labelActivity, 7, true, 'headGroup');
            $this->nextLine();

            foreach ($dataActivity['subgroup'] as $labelLot=>$datalot) {
                $this->drawCell('', 1, true);
                $this->drawCell($labelLot, 6, true, 'headSubGroup');

                foreach ($datas['daysInfos'] as $i=>$day) {
                    $dayKey = $i<10 ? "0$i" : "$i";
                    $class = 'cellEmpty';
                    $value = '0';

                    if( array_key_exists($dayKey, $datalot['days']) ){
                        $class = 'cellValued';
                        $value = number_format($datalot['days'][$dayKey], 2);
                    }

                    if( $day['locked'] ){
                        $class = 'cellLock';
                        $value = $value == '0' ? '' : $value;
                    }
                    $this->drawCell($value, 0, true, $class);
                }
                $this->drawCell($datalot['total'], 0, true, 'headResearch');

                $this->nextLine();
            }
        }

        foreach ($datas['declarations']['others'] as $otherLabel=>$otherData) {
            $this->drawCell('', 0, true);
            $this->drawCell($otherLabel, 7, true, 'headGroup');
            $this->nextLine();

            foreach ($otherData['subgroup'] as $labelLot=>$dataLot) {
                $this->drawCell('', 1, true);
                $this->drawCell($labelLot, 6, true, 'headSubGroup');

                foreach ($datas['daysInfos'] as $i=>$day) {
                    $dayKey = $i<10 ? "0$i" : "$i";
                    $class = 'cellEmpty';
                    $value = '0';

                    if( array_key_exists($dayKey, $dataLot['days']) ){
                        $class = 'cellValued';
                        $value = $dataLot['days'][$dayKey];
                    }

                    if( $day['locked'] == true ){
                        $class = 'cellLock';
                        $value = $value == '0' ? '' : $value;
                    }
                    $this->drawCell($value, 0, true, $class);
                }
                $this->drawCell($dataLot['total'], 0, true, 'headResearch');

                $this->nextLine();
            }
        }

        $this->nextLine();

        $this->drawCell('', 1, true);
        $this->drawCell("TOTAL / jour", 6, true, 'headSubGroup');

//echo "<pre>"; var_dump($datas); die();

// LIGNE TOTAL
        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(20);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Recherche

        $this->nextLine();
        $this->drawCell("Total", 0, true, 'person');

// LOTS
        $totaux = $datas['totaux'];
        foreach ($datas['wps'] as $wp) {
            $value = $totaux['wps'][$wp['code']];
            $this->drawCell($value, 0, true, 'cellTotalBottom');
        }
        $this->drawCell($totaux['totalMain'], 0, true, 'cellTotalBottom');

        foreach ($datas['ces'] as $ce) {
            $value = $totaux['ce'][$ce];
            $this->drawCell($value, 0, true, 'cellTotalBottom');
        }
        foreach ($datas['othersGroups']['research'] as $r) {
            $value = $totaux['others'][$r['code']];
            $this->drawCell($value, 0, true, 'cellTotalBottom');
        }

        $this->drawCell($totaux['totalResearch'], 0, true, 'cellTotalBottom');

        foreach ($datas['othersGroups']['education'] as $r) {
            $value = $totaux['others'][$r['code']];
            $this->drawCell($value, 0, true, 'cellTotalBottom');
        }

        foreach ($datas['othersGroups']['abs'] as $r) {
            $value = $totaux['others'][$r['code']];
            $this->drawCell($value, 0, true, 'cellTotalBottom');
        }

        foreach ($datas['othersGroups']['other'] as $r) {
            $value = $totaux['others'][$r['code']];
            $this->drawCell($value, 0, true, 'cellTotalBottom');
        }

        $this->drawCell($totaux['totalWork'], 0, true, 'cellTotalBottom');
        $this->drawCell($totaux['total'], 0, true, 'cellTotalBottom');

        $this->nextLine();
        $this->nextLine();


        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(4);
        $this->nextLine();

        $widthPerson = floor(($fullWidth - 2)/4);
        $widthComment = floor(($fullWidth - 2)/4)*2;

        foreach ($datas['foo'] as $person=>$line) {

            $comment = "";

            if( array_key_exists($person, $datas['comments']) ){
                foreach ($datas['comments'][$person] as $key=>$content) {
                    $comment .= $content['comment']."\n";
                }
            }

            $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(60);
            $this->drawCell("", 0, true );
            $this->drawCell($person, $widthPerson, true, 'personComment');
            $this->drawCell($comment, $widthComment, true, 'comment');

            $this->nextLine();
            $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(4);
            $this->nextLine();
        }


        $this->autoSizeColumns();


        $this->getActiveSheet()->getPageSetup()
            ->setFitToPage(true)
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        if( $outputFormat == 'excel' ) {
            $this->generate($filename.'.xlsx');
        } else {
            $this->generatePdf($filename.'.pdf');
        }
    }

}