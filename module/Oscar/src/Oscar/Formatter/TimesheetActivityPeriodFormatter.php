<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 27/05/19
 * Time: 11:31
 */

namespace Oscar\Formatter;

use Oscar\Formatter\Utils\SpreadsheetStyleUtils;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class TimesheetActivityPeriodFormatter
{
    private $currentLineIndex;
    private $currentColIndex;
    private $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    private $spreadsheet;
    private $jumpCol = 0;
    private $styles;
    private $activeSheet;

    private $width = 0;
    private $height = 0;


    public function __construct()
    {
        $this->currentColIndex = 0;
        $this->currentLineIndex = 1;
        $this->spreadsheet = new Spreadsheet();
        $this->styles = [];

        // Styles
        $this->addStyle("entete", SpreadsheetStyleUtils::getInstance()->getEntete());
        $this->addStyle("labelTitle", SpreadsheetStyleUtils::getInstance()->getLabelTitle());
        $this->addStyle("labelValue", SpreadsheetStyleUtils::getInstance()->getLabelValue());
        $this->addStyle("total", SpreadsheetStyleUtils::getInstance()->getTotal());
        $this->addStyle("headResearch", SpreadsheetStyleUtils::getInstance()->headResearch());
        $this->addStyle("headAbs", SpreadsheetStyleUtils::getInstance()->headAbs());
        $this->addStyle("headEducation", SpreadsheetStyleUtils::getInstance()->headEducation());
        $this->addStyle("headOther", SpreadsheetStyleUtils::getInstance()->headOther());
        $this->addStyle("withValue", SpreadsheetStyleUtils::getInstance()->withValue());
        $this->addStyle("cellTotalBottom", SpreadsheetStyleUtils::getInstance()->cellTotalBottom());
        $this->addStyle("noValue", SpreadsheetStyleUtils::getInstance()->noValue());
        $this->addStyle("comment", SpreadsheetStyleUtils::getInstance()->comment());
        $this->addStyle("personComment", SpreadsheetStyleUtils::getInstance()->personComment());
        $this->addStyle("person", SpreadsheetStyleUtils::getInstance()->person());
        $this->addStyle("totalColumn", SpreadsheetStyleUtils::getInstance()->totalColumn());

    }

    public function addStyle($name, $options)
    {
        $this->styles[$name] = $options;
    }

    /**
     * Déplace le curseur d'écriture à la colonne suivante
     * @return $this
     */
    public function nextCol()
    {
        $this->currentColIndex += $this->jumpCol + 1;
        $this->jumpCol = 0;
        $this->width = max($this->width, $this->currentColIndex + 1);
        return $this;
    }

    public function autoSizeColumns()
    {
        for ($i = 0; $i < $this->width; $i++) {
            $col = $this->getColStr($i);
            $this->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * @return $this
     */
    public function nextLine()
    {
        $this->currentLineIndex++;
        $this->currentColIndex = 0;
        return $this;
    }

    /**
     * @param $letter
     * @return $this
     */
    public function setCol($letter)
    {
        $this->currentColIndex = array_search($letter, $this->letters);
        return $this;
    }

    public function getCurrentLine()
    {
        return $this->currentLineIndex;
    }

    /**
     * @return string
     */
    public function getCurrentCol()
    {
        return $this->getColStr($this->currentColIndex);
    }

    /**
     * @param $index
     * @return string
     */
    private function getColStr($index)
    {
        $out = "";
        $a = floor($index / count($this->letters));
        if ($a > 0) {
            $out .= $this->letters[$a - 1];
        }
        $b = $index % count($this->letters);
        $out .= $this->letters[$b];
        return "$out";
    }

    /**
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getActiveSheet()
    {
        if( $this->activeSheet == null )
            $this->activeSheet = $this->spreadsheet->getActiveSheet();

        return $this->activeSheet;
    }




    /**
     * @return string
     */
    public function getCurrentCellPosition()
    {
        return $this->getCurrentCol() . "" . $this->currentLineIndex;
    }

    /**
     * @return \PhpOffice\PhpSpreadsheet\Style\Style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getCurrentStyle()
    {
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
    public function drawCell($content, $colspan = 0, $nextCol = true, $style = null)
    {

        // Styles
        if ($style != null) {
            if (!array_key_exists($style, $this->styles)) {
                throw new Exception("Style '$style' non référencé'");
            }

            $this->getActiveSheet()->getStyle($this->getCurrentCellPosition())->applyFromArray($this->styles[$style]);
        }

        $this->getActiveSheet()->setCellValue($this->getCurrentCellPosition(), $content);

        if ($colspan > 0) {
            $colA = $this->getCurrentCol();
            $colB = $this->getColStr($this->currentColIndex + $colspan);
            $line = $this->getCurrentLine();
            $merge = sprintf('%s%s:%s%s', $colA, $line, $colB, $line);
            $this->getActiveSheet()->mergeCells($merge);
            $this->jumpCol = $colspan;
        }

        if ($nextCol) {
            $this->nextCol();
        }
        return $this;
    }

    /**
     * @param $path
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function generate($filename)
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        die();
    }

    public function generatePdf($filename)
    {
        //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // TODO orientation paysage avec DOMPdf
        $writer = IOFactory::createWriter($this->spreadsheet, 'Dompdf');
        $writer->save('php://output');
        die();
    }

    public function newWorksheetPeriod($datas)
    {
        $this->currentColIndex = 0;
        $this->currentLineIndex = 1;

        $workSheetName = $datas['period']['periodLabel'];
        $workSheet = new Worksheet($this->spreadsheet, $workSheetName);
        $this->activeSheet = $workSheet;
        $this->spreadsheet->addSheet($workSheet, 0);


        $colorResearchBG = 'ebf8f5';
        $colorEducationBG = 'ecf6e5';
        $colorAbsBG = 'faefea';
        $colorOtherBG = 'f8faea';

        $wpWidth = count($datas['wps']);
        $ceWidth = count($datas['ces']);
        $educationWidth = count($datas['othersGroups']['education']);
        $absWidth = count($datas['othersGroups']['abs']);
        $otherWidth = count($datas['othersGroups']['other']);
        $researchWidth = count($datas['othersGroups']['research']);

        $fullWidth = $wpWidth + $ceWidth + $educationWidth + $absWidth + $otherWidth + $researchWidth + 3;


        $sizing = floor(($fullWidth - 4) / 4);


        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(40);
        $this->drawCell("FEUILLE de TEMPS", $fullWidth, true, 'entete');
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
        foreach ($datas['wps'] as $wp) {
            $this->drawCell($wp['code'], 0, true, 'headResearch');
        }
        $this->drawCell('Total', 0, true, 'headResearch');

        foreach ($datas['ces'] as $ce) {
            $this->drawCell($ce, 0, true, 'headResearch');
        }
        foreach ($datas['othersGroups']['research'] as $r) {
            $this->drawCell($r['label'], 0, true, 'headResearch');
        }

        $this->drawCell('Total', 0, true, 'headResearch');

        foreach ($datas['othersGroups']['education'] as $r) {
            $this->drawCell($r['label'], 0, true, 'headEducation');
        }

        foreach ($datas['othersGroups']['abs'] as $r) {
            $this->drawCell($r['label'], 0, true, 'headAbs');
        }

        foreach ($datas['othersGroups']['other'] as $r) {
            $this->drawCell($r['label'], 0, true, 'headOther');
        }

        $this->drawCell('Total actif', 0, true, 'person');

        $colSign = $this->getCurrentCol();
        $this->drawCell('TOTAL', 0, true, 'person');
        $this->drawCell('Signature', 0, true, 'person');

        $this->nextLine();

        foreach ($datas['foo'] as $person => $line) {

            $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(20);

            // Recherche
            $this->drawCell($person, 0, true, 'person');

            foreach ($datas['wps'] as $wp) {
                $code = $wp['code'];
                $this->getCurrentStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("ff$colorResearchBG");
                $this->drawCell(number_format($line['main'][$code], 2), 0, true, $line['main'][$code] ? 'withValue' : 'noValue');
            }

            $this->drawCell($line['totalMain'], 0, true, 'totalColumn');

            foreach ($datas['ces'] as $ce) {
                $this->getCurrentStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("ff$colorResearchBG");
                $this->drawCell($line['ce'][$ce], 0, true, $line['ce'][$ce] ? 'withValue' : 'noValue');
            }

            foreach ($datas['othersGroups']['research'] as $r) {
                $this->getCurrentStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("ff$colorResearchBG");
                $this->drawCell($line['others'][$r['code']], 0, true, $line['others'][$r['code']] ? 'withValue' : 'noValue');
            }
            $this->getCurrentStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("ff$colorResearchBG");
            $this->drawCell($line['totalResearch'], 0, true, 'totalColumn');

            foreach ($datas['othersGroups']['education'] as $r) {
                $this->getCurrentStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("ff$colorEducationBG");
                $this->drawCell($line['others'][$r['code']], 0, true, $line['others'][$r['code']] ? 'withValue' : 'noValue');
            }

            foreach ($datas['othersGroups']['abs'] as $r) {
                $this->getCurrentStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("ff$colorAbsBG");
                $this->drawCell($line['others'][$r['code']], 0, true, $line['others'][$r['code']] ? 'withValue' : 'noValue');
            }

            foreach ($datas['othersGroups']['other'] as $r) {
                $this->getCurrentStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("ff$colorOtherBG");
                $this->drawCell($line['others'][$r['code']], 0, true, $line['others'][$r['code']] ? 'withValue' : 'noValue');
            }

            $this->drawCell($line['totaux']['totalWork'], 0, true, 'totalColumn');
            $this->drawCell($line['totaux']['total'], 0, true, 'totalColumn');
            $this->drawCell(' ', 0, true, 'person');
            $this->nextLine();
        }

// LIGNE TOTAL
        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(20);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Recherche
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

        $widthPerson = floor(($fullWidth - 2) / 4);
        $widthComment = floor(($fullWidth - 2) / 4) * 2;

        foreach ($datas['foo'] as $person => $line) {

            $comment = "";

            if (array_key_exists($person, $datas['comments'])) {
                foreach ($datas['comments'][$person] as $key => $content) {
                    $comment .= $content['comment'] . "\n";
                }
            }

            $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(60);
            $this->drawCell("", 0, true);
            $this->drawCell($person, $widthPerson, true, 'personComment');
            $this->drawCell($comment, $widthComment, true, 'comment');

            $this->nextLine();
            $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(4);
            $this->nextLine();
        }


        $this->autoSizeColumns();
        $this->getActiveSheet()->getColumnDimension($colSign)->setAutoSize(false)->setWidth('20');

        $this->getActiveSheet()->getPageSetup()
            ->setFitToPage(true)
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        return $workSheet;
    }


    public function output($datas, $outputFormat = 'excel')
    {

        if( array_key_exists('periods', $datas) ){
            foreach ($datas['periods'] as $datasPeriod) {
                $this->newWorksheetPeriod($datasPeriod);
            }
            $filename = "repport-full";
            $this->spreadsheet->setIndexByName('Worksheet', 0);
        } else {
            $workSheet = $this->newWorksheetPeriod($datas);
            $filename = $datas['activity']['numOscar'] . '_' . $datas['period']['year'] . '-' . $datas['period']['month'];
        }


        if ($outputFormat == 'excel') {
            $this->generate($filename . '.xlsx');
        } else {
            $this->generatePdf($filename . '.pdf');
        }
        ///////// LA classe à Dalas



    }

}