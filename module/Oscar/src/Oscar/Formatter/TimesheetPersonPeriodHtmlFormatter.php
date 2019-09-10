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
 * Sortie HTML pour les données de déclaration d'une personne pour une période (Mois : YYYY-MM) donné.
 *
 * Class TimesheetPersonPeriodFormatterHtml
 * @package Oscar\Formatter
 */
class TimesheetPersonPeriodHtmlFormatter
{

    /** @var string Emplacement du gabarit */
    private $templatePath;

    public function __construct($templatePath){
        $this->templatePath = $templatePath;
    }

    public function render( array $datas ){
        if( !$this->templatePath ){
            throw new OscarException(_("L'emplacement du gabarit de mise en forme des feuilles de temps individuelles est mal configuré."));
        }
        $nbrJours = count($datas['daysInfos']);
        $width = $nbrJours +2;

        $colSize4 = ceil(($nbrJours-3) / 4);
        $padding = $nbrJours - ($colSize4*4);

        ob_start();
        require $this->templatePath;
        return ob_get_clean();
    }
}