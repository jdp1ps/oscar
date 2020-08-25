<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/18
 * Time: 11:52
 */

namespace Oscar\Formatter;


class CSVDownloader
{

    public function downloadCSVToExcel($csvPath){
        $xlsPath = $csvPath.'.xls';

        $doc = new \PHPExcel();

        /** @var \PHPExcel_Worksheet $sheet */
        $sheet = $doc->getActiveSheet();


        $handler = fopen($csvPath, 'r');
        $row = 1;
        if (($handle = fopen($csvPath, "r")) !== FALSE) {
            $cell = 1;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                $num = count($data);
                for ($c=0; $c < $num; $c++) {
                    $value = $data[$c];
                    if(preg_match('/[0-9]*,[0-9]*/', $value)){
                       $sheet->setCellValueExplicitByColumnAndRow($c, $row, $value, \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    }

                    elseif (preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $value)) {
                       $sheet->setCellValueExplicitByColumnAndRow($c, $row, $value, \PHPExcel_Cell_DataType::TYPE_STRING);
                    }

                    else {
                        $sheet->setCellValueExplicitByColumnAndRow($c, $row, $value, \PHPExcel_Cell_DataType::TYPE_STRING);
                    }
                }
                $row++;
            }
            fclose($handle);
        }
        $writer = \PHPExcel_IOFactory::createWriter($doc, 'Excel5');
        $writer->save($xlsPath);
        $this->download($xlsPath, 'xls', 'application/vnd.ms-excel');


        @unlink($xlsPath);

        return $xlsPath;
    }

    public function downloadCSV( $csvPath ){
        $this->download($csvPath, 'csv', 'text/csv');
        @unlink($csvPath);
    }

    private function download($path, $extension, $mime){
        header('Content-Disposition: attachment; filename=oscar-export.' . $extension);
        header('Content-Length: ' . filesize($path));
        header('Content-type: '.$mime);
        echo file_get_contents($path);
    }
}