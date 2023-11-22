<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 21/08/19
 * Time: 16:51
 */

namespace Oscar\Formatter\Timesheet;

use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplateMapResolver;
use Oscar\Exception\OscarException;
use Oscar\Formatter\Output\OutputHtmlStrategy;

/**
 * Sortie HTML pour les données de déclaration d'une personne pour une période (Mois : YYYY-MM) donné.
 *
 * Class TimesheetPersonPeriodFormatterHtml
 * @package Oscar\Formatter
 */
class TimesheetPeriodHtmlFormatter
{

    /** @var string Emplacement du gabarit */
    private string $templatePath;

    public function __construct(string $templatePath)
    {
        $this->templatePath = $templatePath;
    }

    public function render(array $datas): string
    {
        if (!$this->templatePath) {
            throw new OscarException(
                _("L'emplacement du gabarit de mise en forme des feuilles de temps par période est mal configuré.")
            );
        }

        extract($datas);
        ob_start();
        include $this->templatePath;
        return ob_get_clean();
    }

    public function output(array $datas):void {
        $filename = 'Repport-'.$datas['activity']['num'].'.html';
        $html = $this->render($datas);
        $output = new OutputHtmlStrategy();
        $output->output($html, $filename);
    }

}