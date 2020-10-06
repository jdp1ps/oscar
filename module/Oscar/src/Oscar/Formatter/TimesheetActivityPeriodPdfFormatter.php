<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/09/19
 * Time: 15:28
 */

namespace Oscar\Formatter;

use Dompdf\Dompdf;
use Oscar\Formatter\File\IHtmlToPdfFormatter;

/**
 * Effectue la mise en forme PDF des données.
 * Ce formatteur s'appuis sur le résultat HTML pour générer le PDF avec DOMPDF.
 *
 * @package Oscar\Formatter
 */
class TimesheetActivityPeriodPdfFormatter extends TimesheetActivityPeriodHtmlFormatter
{
    public function render( array $datas, $method ){

        /** @var IHtmlToPdfFormatter $transformer */
        $transformer = new $method;

        /** @var string $html Contenu HTML du document */
        $html = parent::render($datas, null);

        /** @var string $filename Nom du fichier */
        $filename = $datas['activity']['numOscar'].'-'.$datas['period']['year'].'-'.$datas['period']['month'];

        $transformer->setOrientation(IHtmlToPdfFormatter::ORIENTATION_LANDSCAPE)
            ->convert($html, $filename, true);
        /*

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($filename);
        */
        return;
    }
}