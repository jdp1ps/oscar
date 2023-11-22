<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/09/19
 * Time: 15:28
 */

namespace Oscar\Formatter;

use Oscar\Formatter\Output\OutputWkhtmltopdfStrategy;
use Oscar\Formatter\Timesheet\TimesheetActivityPeriodHtmlFormatter;

/**
 * Effectue la mise en forme PDF des données.
 * Ce formatteur s'appuis sur le résultat HTML pour générer le PDF avec DOMPDF.
 *
 * @package Oscar\Formatter
 */
class TimesheetActivityPeriodPdfFormatter extends TimesheetActivityPeriodHtmlFormatter
{
    const FILENAME_TPL = 'Activity-Period-%s---%s-%s.pdf';

    /**
     * @param array $datas
     * @return void
     */
    public function stream(array $datas): void
    {
        $filename = sprintf(self::FILENAME_TPL,
                            $datas['activity']['numOscar'],
                            $datas['period']['year'],
                            $datas['period']['month']);

        $html = $this->render($datas);

        $renderPdf = new OutputWkhtmltopdfStrategy();
        $renderPdf->output($html, $filename, OutputWkhtmltopdfStrategy::ORIENTATION_LANDSCAPE);
    }
}