<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/18
 * Time: 11:52
 */

namespace Oscar\Formatter;


use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CSVDownloader
{

    public function downloadCSVToExcel($csvPath)
    {
//        echo "<table style='font-size: 12px' border='1'>";
//        for( $i = 0; $i < 25; $i++ ){
//            echo "<tr>";
//            for( $j = 0; $j < 60; $j++ ){
//                $cell = $this->getCurrentCell();
//                echo "<td>$cell</td>";
//                $this->nextCol();
//
//            }
//            $this->nextLine();
//            echo "</tr>";
//        }
//        echo "</table>";

        $xlsPath = $csvPath . '.xls';
        $doc = new Spreadsheet();
        $this->reset();
        //ob_start();
        //Cell::setValueBinder(new AdvancedValueBinder());
        if (($handle = fopen($csvPath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 10000, "\t")) !== FALSE) {
                $num = count($data);
                for ($c = 0; $c < $num; $c++) {
                    $cell = $this->getCurrentCell();
                    $value = $data[$c];
                    $doc->getActiveSheet()->setCellValue($cell, $value);
                    $this->nextCol();
                    if (preg_match('/([0-9 ]*),([0-9]{2})/', $value)) {
                        // Il faut convertir en "vrai" nombre
                        $value = str_replace(',', '.', $value);
                        $doc->getActiveSheet()->setCellValue($cell, $value);
                        $doc->getActiveSheet()->getStyle($cell)
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_ACCOUNTING_EUR);
                    } elseif (preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $value)) {
                        // Traitement des dates
                        $dateTimeValue = new \DateTime($value);
                        $dateValue = Date::PHPToExcel($dateTimeValue);
                        $doc->getActiveSheet()->setCellValue($cell, $dateValue);
                        $doc->getActiveSheet()->getStyle($cell)
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                        //$sheet->setCellValueByColumnAndRow($c, $row, \PHPExcel_Shared_Date::PHPToExcel($value));
                    } else {
                        // foo
                    }
                }
                $this->nextLine();
            }
            fclose($handle);
        }
        $writer = new Xlsx($doc);
        $writer->save($xlsPath);
        $this->download($xlsPath, 'xlsx', 'application/vnd.ms-excel');
        die("Write into '$xlsPath'");
        @unlink($xlsPath);

//        return $xlsPath;
    }

    public function downloadCSV($csvPath)
    {
        $this->download($csvPath, 'csv', 'text/csv');
        @unlink($csvPath);
    }

    private function download($path, $extension, $mime)
    {
        header('Content-Disposition: attachment; filename=oscar-export.' . $extension);
        header('Content-Length: ' . filesize($path));
        header('Content-type: ' . $mime);
        echo file_get_contents($path);
    }
}