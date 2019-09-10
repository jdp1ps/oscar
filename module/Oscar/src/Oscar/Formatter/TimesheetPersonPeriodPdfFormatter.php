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

/**
 * Sortie PDF pour les données de déclaration d'une personne.
 *
 * Class TimesheetPersonPeriodFormatterHtml
 * @package Oscar\Formatter
 */
class TimesheetPersonPeriodPdfFormatter extends TimesheetPersonPeriodHtmlFormatter
{
    public function render( array $datas ){
        $dompdf = new Dompdf();
        $dompdf->loadHtml(parent::render($datas));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream(array_key_exists('filename', $datas) ? $datas['filename'] : 'OscarTimesheetperiod.pdf');
        return;
    }
}