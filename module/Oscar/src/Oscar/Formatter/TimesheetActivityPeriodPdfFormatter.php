<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/09/19
 * Time: 15:28
 */

namespace Oscar\Formatter;

use Dompdf\Dompdf;

/**
 * Effectue la mise en forme PDF des données.
 * Ce formatteur s'appuis sur le résultat HTML pour générer le PDF avec DOMPDF.
 *
 * @package Oscar\Formatter
 */
class TimesheetActivityPeriodPdfFormatter extends TimesheetActivityPeriodHtmlFormatter
{
    public function render( array $datas ){
        $html = parent::render($datas);
        $filename = $datas['activity']['numOscar'].'-'.$datas['period']['year'].'-'.$datas['period']['month'];
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($filename);
        return;
    }
}