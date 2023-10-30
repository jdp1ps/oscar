<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 21/08/19
 * Time: 16:51
 */

namespace Oscar\Formatter;

use Oscar\Exception\OscarException;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplateMapResolver;

/**
 * Sortie HTML pour les données de déclaration d'une personne pour une période (Mois : YYYY-MM) donné.
 *
 * Class TimesheetPersonPeriodFormatterHtml
 * @package Oscar\Formatter
 */
class TimesheetPeriodHtmlFormatter
{

    /** @var string Emplacement du gabarit */
    private $templatePath;

    /** @var RendererInterface Service de rendering (Renderer Zend) */
    private $renderer;

    public function __construct($templatePath, RendererInterface $renderer){
        $this->templatePath = $templatePath;
        $this->renderer = $renderer;
    }

    public function render( array $datas ){
        if( !$this->templatePath ){
            throw new OscarException(_("L'emplacement du gabarit de mise en forme des feuilles de temps par période est mal configuré."));
        }
        //var_dump($datas); die();
        $datas['nbrJours'] = $nbrJours = count($datas['daysInfos']);
        $datas['width'] = $width = $nbrJours +2;
        $datas['colSize4'] = $colSize4 = ceil(($nbrJours-3) / 4);
        $datas['padding'] = $nbrJours - ($colSize4*4);

        $view = new ViewModel($datas);
        $view->setTemplate('timesheet_period_synthesis');
        $view->setTerminal(true);

        $resolver = new AggregateResolver();
        $map = new TemplateMapResolver([
            'timesheet_period_synthesis' => $this->templatePath
        ]);
        $this->renderer->setResolver($resolver);
        $resolver->attach($map);

        return $this->renderer->render($view);
    }
}