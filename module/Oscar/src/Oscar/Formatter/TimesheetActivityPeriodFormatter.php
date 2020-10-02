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
use PhpOffice\PhpSpreadsheet\Style\Fill;
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
    private $synthesis;
    private $SynthesisFormula;
    private $allCes;

    private $width = 0;
    private $height = 0;


    public function __construct()
    {
        $this->currentColIndex = 0;
        $this->currentLineIndex = 1;
        $this->spreadsheet = new Spreadsheet();
        $this->styles = [];
        $this->synthesis = [];
        $this->SynthesisFormula = [];
        $this->allCes = [];

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
                throw new \Exception("Style '$style' non référencé'");
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

    public function stylisation($theme){
        $color = "ffefefef";
        switch ($theme) {
            case 'research' : $color = 'ffebf8f5'; break;
            case 'education' : $color = 'ffecf6e5'; break;
            case 'abs' : $color = 'fffaefea'; break;
            case 'other' : $color = 'fff8faea'; break;
        }
        $this->getCurrentStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB("$color");
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

        $fullWidth = $wpWidth + $ceWidth + $educationWidth + $absWidth + $otherWidth + $researchWidth + 5;


        $sizing = floor(($fullWidth - 4) / 4);


        $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(40);
        $this->drawCell("FEUILLE de TEMPS " . $datas['period']['periodLabel'], $fullWidth, true, 'entete');
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

            if( !array_key_exists($person, $this->synthesis) ){
                $this->synthesis[$person] = [];
                $this->synthesis[$person]['wps'] = [];
                foreach ($datas['wps'] as $wp) {
                    $code = $wp['code'];
                    $this->synthesis[$person]['wps'][$code] = [];
                }
                $this->synthesis[$person]['totalMain'] = [];

                $this->synthesis[$person]['ces'] = [];
                foreach ($datas['ces'] as $ce) {
                    if( !in_array($ce, $this->allCes) ){
                        $this->allCes[] = $ce;
                    }
                    $this->synthesis[$person]['ces'][$ce] = [];
                }
                $this->synthesis[$person]['totalCes'] = [];
                $this->synthesis[$person]['totalResearch'] = [];

                $this->synthesis[$person]['othersGroups'] = [
                    'research' => [],
                    'education' => [],
                    'abs' => [],
                    'other' => [],
                ];
                foreach ($datas['othersGroups'] as $group=>$dataGroup ) {
                    $this->synthesis[$person]['othersGroups'][$group] = [];
                    foreach ($dataGroup as $subGroup=>$subGroupData) {
                        $this->synthesis[$person]['othersGroups'][$group][$subGroup] = [];
                    }
                }
            }

            $this->getActiveSheet()->getRowDimension($this->getCurrentLine())->setRowHeight(20);

            // Recherche
            $this->drawCell($person, 0, true, 'person');


            foreach ($datas['wps'] as $wp) {
                $code = $wp['code'];
                $this->stylisation('research');
                //$this->synthesis[$person]['wps'][$code][] = sprintf("$'%s'.%s", $workSheetName, $this->getCurrentCellPosition());
                $this->synthesis[$person]['wps'][$code][] = $line['main'][$code];
                $this->drawCell(number_format($line['main'][$code], 2), 0, true, $line['main'][$code] ? 'withValue' : 'noValue');
            }

            //$this->synthesis[$person]['totalMain'][] = sprintf("$'%s'.%s", $workSheetName, $this->getCurrentCellPosition());
            $this->synthesis[$person]['totalMain'][] = $line['totalMain'];
            $this->synthesis[$person]['totalResearch'][] = $line['totalMain'];
            $this->drawCell($line['totalMain'], 0, true, 'totalColumn');


            ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // CONTRACTS EUROPEENS
            foreach ($datas['ces'] as $ce) {
                $this->stylisation('research');
                //$this->synthesis[$person]['ces'][$ce][] = sprintf("$'%s'.%s", $workSheetName, $this->getCurrentCellPosition());
                $this->synthesis[$person]['ces'][$ce][] = $line['ce'][$ce];
                $this->synthesis[$person]['totalCes'][] = $line['ce'][$ce];
                $this->synthesis[$person]['totalResearch'][] = $line['ce'][$ce];
                $this->drawCell($line['ce'][$ce], 0, true, $line['ce'][$ce] ? 'withValue' : 'noValue');
            }

            foreach ($datas['othersGroups']['research'] as $r) {
                $this->stylisation('research');
                $this->synthesis[$person]['othersGroups']['research'][$r['code']][] = $line['others'][$r['code']];
                $this->synthesis[$person]['totalResearch'][] = $line['others'][$r['code']];
                $this->drawCell($line['others'][$r['code']], 0, true, $line['others'][$r['code']] ? 'withValue' : 'noValue');
            }
            $this->stylisation('research');
            $this->drawCell($line['totalResearch'], 0, true, 'totalColumn');

            foreach ($datas['othersGroups']['education'] as $r) {
                $this->stylisation('education');
                $this->synthesis[$person]['othersGroups']['education'][$r['code']][] = $line['others'][$r['code']];
                $this->drawCell($line['others'][$r['code']], 0, true, $line['others'][$r['code']] ? 'withValue' : 'noValue');
            }

            foreach ($datas['othersGroups']['abs'] as $r) {
                $this->stylisation('abs');
                $this->synthesis[$person]['othersGroups']['abs'][$r['code']][] = $line['others'][$r['code']];
                $this->drawCell($line['others'][$r['code']], 0, true, $line['others'][$r['code']] ? 'withValue' : 'noValue');
            }

            foreach ($datas['othersGroups']['other'] as $r) {
                $this->stylisation('other');
                $this->synthesis[$person]['othersGroups']['other'][$r['code']][] = $line['others'][$r['code']];
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
            $datasSynthesis = [];

            $synthesis = null;

            foreach ($datas['periods'] as $datasPeriod) {
//                if( $synthesis == null ){
//                    $synthesis = [];
//                    foreach ($datas['wps'] as $wp) {
//                        $this->drawCell($wp['code'], 0, true, 'headResearch');
//                    }
//                    $this->drawCell('Total', 0, true, 'headResearch');
//
//                    foreach ($datas['ces'] as $ce) {
//                        $this->drawCell($ce, 0, true, 'headResearch');
//                    }
//                    foreach ($datas['othersGroups']['research'] as $r) {
//                        $this->drawCell($r['label'], 0, true, 'headResearch');
//                    }
//
//                    $this->drawCell('Total', 0, true, 'headResearch');
//
//                    foreach ($datas['othersGroups']['education'] as $r) {
//                        $this->drawCell($r['label'], 0, true, 'headEducation');
//                    }
//
//                    foreach ($datas['othersGroups']['abs'] as $r) {
//                        $this->drawCell($r['label'], 0, true, 'headAbs');
//                    }
//
//                    foreach ($datas['othersGroups']['other'] as $r) {
//                        $this->drawCell($r['label'], 0, true, 'headOther');
//                    }
//
//                    $this->drawCell('Total actif', 0, true, 'person');
//
//                    $colSign = $this->getCurrentCol();
//                    $this->drawCell('TOTAL', 0, true, 'person');
//                    foreach ($datasPeriod['foo'] as $person=>$datas) {
//                        if( !array_key_exists($person, $datasSynthesis) ){
//                            $datasSynthesis[$person] = [];
//                        }
//                    }
//                }

                $this->newWorksheetPeriod($datasPeriod);

            }

            $datas = $datasPeriod;

            $filename = "repport-full";
            $synthese = $this->spreadsheet->getSheetByName('Worksheet');
            $synthese->setTitle("Synthèse");
            $this->spreadsheet->setActiveSheetIndexByName('Synthèse');
            $this->activeSheet = $this->spreadsheet->getActiveSheet();

            $this->currentColIndex = 0;
            $this->currentLineIndex = 1;
            $this->drawCell("SYNTHèSE pour l'ACTIVITé", 30, true, 'entete');
            $this->nextLine();
            $this->nextLine();
            $this->nextCol();
            foreach ($datas['wps'] as $wp) {
                $this->drawCell($wp['code'], 0, true, 'headResearch');
            }
            $this->drawCell('Total', 0, true, 'headResearch');


            $this->drawCell("Projets CE", 0, true, 'headResearch');

            foreach ($datas['othersGroups']['research'] as $r) {
                $this->drawCell($r['label'], 0, true, 'headResearch');
            }

            $this->drawCell('Total Recherche', 0, true, 'headResearch');

            foreach ($datas['othersGroups']['education'] as $r) {
                $this->drawCell($r['label'], 0, true, 'headEducation');
            }
            if( count($datas['othersGroups']['education']) > 1 ){
                $this->drawCell("Total Enseignement", 0, true, 'headEducation');
            }

            foreach ($datas['othersGroups']['abs'] as $r) {
                $this->drawCell($r['label'], 0, true, 'headAbs');
            }
            if( count($datas['othersGroups']['abs']) > 1 ){
                $this->drawCell("Total absent", 0, true, 'headAbs');
            }

            foreach ($datas['othersGroups']['other'] as $r) {
                $this->drawCell($r['label'], 0, true, 'headOther');
            }
            if( count($datas['othersGroups']['other']) > 1 ){
                $this->drawCell("Total autre", 0, true, 'headOther');
            }

            $this->drawCell('Total actif', 0, true, 'person');

            $colSign = $this->getCurrentCol();
            $this->drawCell('TOTAL', 0, true, 'person');

            $this->nextLine();
            $this->nextLine();

            foreach ($this->synthesis as $person=>$personDatas) {

                // --- PROJET PRINCIPAL
                $this->drawCell($person, 0, true, 'person');
                $startSum = $this->getCurrentCellPosition();
                foreach ($personDatas['wps'] as $wp => $cells) {
                    if( count($cells) ){
                        // $formula = sprintf("=SOMME(%s)", implode(';', $cells));
                        $formula = array_sum($cells);
                    } else {
                        $formula = "0";
                    }
                    $this->stylisation('research');
                    $this->drawCell($formula, 0, true, 'withValue');
                }
                $endSum = $this->getCurrentCellPosition();
                $formula = sprintf('=SUM(%s:%s)', $startSum, $endSum);
                $sumResearch = [];
                $this->drawCell($formula, 0, true, 'withValue');
                $sumResearch[] = $this->getCurrentCellPosition();


                // --- AUTRES PROJETS avec Feuille de temps
                $this->stylisation('research');
                $formula = array_sum($personDatas['totalCes']);
                $this->drawCell($formula, 0, true, 'withValue');
                $sumResearch[] = $this->getCurrentCellPosition();

                // --- Autres recherches
                $startSum = $this->getCurrentCellPosition();
                foreach ($personDatas['othersGroups']['research'] as $otherResearch => $values) {
                    $formula = array_sum($values);
                    $this->stylisation('research');
                    $this->drawCell($formula, 0, true, 'withValue');
                }
                $endSum = $this->getCurrentCellPosition();
                if( count($personDatas['othersGroups']['research']) > 1) {
                    $this->stylisation('research');
                    $formula = sprintf('=SUM(%s:%s)', $startSum, $endSum);
                    $this->drawCell($formula, 0, true, 'withValue');
                }

                $this->stylisation('research');
                $formula = array_sum($personDatas['totalResearch']);
                $this->drawCell($formula, 0, true, 'withValue');

                // --- Education
                $startSum = $this->getCurrentCellPosition();
                foreach ($personDatas['othersGroups']['education'] as $subGroupKey => $subGroupValues) {
                    $formula = array_sum($subGroupValues);
                    $this->stylisation('education');
                    $this->drawCell($formula, 0, true, 'withValue');
                }
                $endSum = $this->getCurrentCellPosition();
                if( count($personDatas['othersGroups']['education']) > 1) {
                    $this->stylisation('education');
                    $formula = sprintf('=SUM(%s:%s)', $startSum, $endSum);
                    $this->drawCell($formula, 0, true, 'withValue');
                }

                // --- ABS
                $startSum = $this->getCurrentCellPosition();
                foreach ($personDatas['othersGroups']['abs'] as $subGroupKey => $subGroupValues) {
                    $formula = array_sum($subGroupValues);
                    $this->stylisation('abs');
                    $this->drawCell($formula, 0, true, 'withValue');
                }
                $endSum = $this->getCurrentCellPosition();
                if( count($personDatas['othersGroups']['abs']) > 1) {
                    $this->stylisation('abs');
                    $formula = sprintf('=SUM(%s:%s)', $startSum, $endSum);
                    $this->drawCell($formula, 0, true, 'withValue');
                }

                // --- ABS
                $startSum = $this->getCurrentCellPosition();
                foreach ($personDatas['othersGroups']['other'] as $subGroupKey => $subGroupValues) {
                    $formula = array_sum($subGroupValues);
                    $this->stylisation('others');
                    $this->drawCell($formula, 0, true, 'withValue');
                }
                $endSum = $this->getCurrentCellPosition();
                if( count($personDatas['othersGroups']['other']) > 1) {
                    $this->stylisation('others');
                    $formula = sprintf('=SUM(%s:%s)', $startSum, $endSum);
                    $this->drawCell($formula, 0, true, 'withValue');
                }

                // Total Recherche
                //dump($personDatas); die();
                $this->nextLine();
            }

            $this->autoSizeColumns();



            $this->spreadsheet->setIndexByName('Synthèse', 0);
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