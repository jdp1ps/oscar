<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 21/08/19
 * Time: 16:51
 */

namespace Oscar\Formatter;

use Dompdf\Dompdf;
use Oscar\Exception\OscarException;

class TimesheetPersonPeriodFormatter2
{

    private $templatePath;

    public function __construct($templatePath){
        $this->templatePath = $templatePath;
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

        $nbrJours = count($datas['daysInfos']);
        $width = $nbrJours +2;

        $colSize4 = ceil(($nbrJours-3) / 4);
        $padding = $nbrJours - ($colSize4*4);

        // emplacement du gabarit
        $templatePath = $this->templatePath;

        if( !$templatePath ){
            throw new OscarException(_("L'emplacement du gabarit de mise en forme des feuilles de temps individuelles est mal configuré."));
        }
        ob_start();

        require $templatePath;

        $html = ob_get_clean();
        if( $outputFormat == 'html' ){
            die($html);
        }
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($datas['filename']);
        die();
    }
}