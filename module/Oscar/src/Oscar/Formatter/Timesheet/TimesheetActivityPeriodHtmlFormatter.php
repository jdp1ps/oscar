<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/09/19
 * Time: 15:12
 */

namespace Oscar\Formatter\Timesheet;

use Oscar\Formatter\Output\OutputHtmlStrategy;

/**
 * Effectue la mise en forme HTML des données de déclaration pour une activité pour une période.
 * A noter que pour le moment, la période est mensuelle, mais l'affichage du cumule est conditionné
 * par les données envoyées.
 *
 * @package Oscar\Formatter
 */
class TimesheetActivityPeriodHtmlFormatter
{
    const FILENAME_TPL = 'Export-activity-period-%s---%s-%s.html';

    /** @var string Emplacement du gabarit fournis par la configuration */
    private string $templatePath;

    public function __construct(string $templatePath)
    {
        $this->templatePath = $templatePath;
    }

    /**
     * @param array $datas
     * @return string
     */
    public function render(array $datas): string
    {
        extract($datas);
        ob_start();
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