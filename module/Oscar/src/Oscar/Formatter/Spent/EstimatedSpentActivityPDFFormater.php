<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 05/03/20
 * Time: 15:09
 */

namespace Oscar\Formatter\Spent;

use Dompdf\Dompdf;

class EstimatedSpentActivityPDFFormater extends EstimatedSpentActivityHTMLFormater
{
    public function format($options=[])
    {
        $filename = sprintf("%s - dépenses-prévisionnelles.pdf", $this->datas['activity']->getOscarNum());

        $dompdf = new Dompdf();
        $dompdf->loadHtml(parent::format());
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($filename);
        return;
    }
}