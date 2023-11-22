<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 21/08/19
 * Time: 16:51
 */

namespace Oscar\Formatter\Timesheet;

use Oscar\Exception\OscarException;
use Oscar\Formatter\Output\OutputHtmlStrategy;

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

    public function __construct($templatePath)
    {
        $this->templatePath = $templatePath;
    }

    public function render(array $datas): string
    {
        if (!$this->templatePath) {
            throw new OscarException(
                _("L'emplacement du gabarit de mise en forme des feuilles de temps individuelles est mal configuré.")
            );
        }
        //var_dump($datas); die();
        $datas['nbrJours'] = $nbrJours = count($datas['daysInfos']);
        $datas['width'] = $width = $nbrJours + 2;
        $datas['colSize4'] = $colSize4 = ceil(($nbrJours - 3) / 4);
        $datas['padding'] = $nbrJours - ($colSize4 * 4);

        ob_start();
        extract($datas);
        include $this->templatePath;
        return ob_get_clean();
    }

    public function output(array $datas):void {
        $filename = $datas['filename'].'.html';
        $html = $this->render($datas);
        $output = new OutputHtmlStrategy();
        $output->output($html, $filename);
    }
}