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

        $reader = \PHPExcel_IOFactory::createReader('CSV');
        $csvDatas = $reader->load($csvPath);

        $writer = \PHPExcel_IOFactory::createWriter($csvDatas, 'Excel5');
        $writer->save($xlsPath);

        $this->download($xlsPath, 'xls', 'application/vnd.ms-excel');

        @unlink($csvPath);
        @unlink($xlsPath);

        return $xlsPath;
    }

    public function downloadCSV( $csvPath ){
        $this->download($csvPath, 'csv', 'text/csv');
    }

    private function download($path, $extension, $mime){
        header('Content-Disposition: attachment; filename=oscar-export.' . $extension);
        header('Content-Length: ' . filesize($path));
        header('Content-type: '.$mime);
        echo file_get_contents($path);
    }
}