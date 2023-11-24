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
        $xlsPath = $csvPath . '.xls';

        $doc = new \PHPExcel();

        /** @var \PHPExcel_Worksheet $sheet */
        $sheet = $doc->getActiveSheet();

        ob_start();

        $re_single_date = '/^([0-9]{4})-((0[1-9])|(1[1-2]))-[0-9]{2}$/';
        $handler = fopen($csvPath, 'r');
        $row = 1;
        //Cell::setValueBinder(new AdvancedValueBinder());
        if (($handle = fopen($csvPath, "r")) !== FALSE) {
            $cell = 1;
            while (($data = fgetcsv($handle, 10000, "\t")) !== FALSE) {
                $num = count($data);
                for ($c = 0; $c < $num; $c++) {
                    $value = $data[$c];
                    if (preg_match('/([0-9 ]*),([0-9]{2})/', $value)) {
                        // Il faut convertir en "vrai" nombre
                        $value = str_replace(',', '.', $value);
                        $cell = $sheet->setCellValueExplicitByColumnAndRow($c, $row, $value, DataType::TYPE_NUMERIC, true);
                        $cell->getStyle()->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR);
                    } elseif (preg_match($re_single_date, $value)) {
                        error_log($value);
                        $sheet->getCellByColumnAndRow($c, $row)->getStyle()
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                        $sheet->setCellValueByColumnAndRow($c, $row, \PHPExcel_Shared_Date::PHPToExcel($value));
                    } else {
                        $sheet->setCellValueExplicitByColumnAndRow($c, $row, $value, DataType::TYPE_STRING);
                    }
                }
                $row++;
            }
            fclose($handle);
        }
        $writer = \PHPExcel_IOFactory::createWriter($doc, "Excel2007");
        $writer->save($xlsPath);
        $this->download($xlsPath, 'xlsx', 'application/vnd.ms-excel');

        @unlink($xlsPath);

        return $xlsPath;
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