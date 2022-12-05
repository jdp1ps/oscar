<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/09/19
 * Time: 15:12
 */

namespace Oscar\Formatter;

use Oscar\Formatter\File\IHtmlToPdfFormatter;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;

/**
 * Effectue la mise en forme HTML des données de déclaration pour une activité pour une période.
 * A noter que pour le moment, la période est mensuelle, mais l'affichage du cumule est conditionné
 * par les données envoyées.
 *
 * @package Oscar\Formatter
 */
class TimesheetActivityPeriodBoundsHtmlFormatter
{
    /** @var string Emplacement du gabarit fournis par la configuration */
    private $templatePath;

    /** @var RendererInterface Service de rendering (Renderer Zend) */
    private $renderer;

    /**
     * TimesheetActivityPeriodHtmlFormatter constructor.
     * @param Emplacement $templatePath
     * @param Service $renderer
     */
    public function __construct(string $templatePath, RendererInterface $renderer)
    {
        $this->templatePath = $templatePath;
        $this->renderer = $renderer;
    }

    public function render( array $datas, $method=null ){
        $view = new ViewModel($datas);
        $view->setTemplate('timesheet_activity_synthesis');
        $view->setTerminal(true);

        $resolver = new AggregateResolver();
        $map = new TemplateMapResolver([
            'timesheet_activity_synthesis' => $this->templatePath
        ]);
        $this->renderer->setResolver($resolver);
        $resolver->attach($map);

        return $this->renderer->render($view);
    }


}