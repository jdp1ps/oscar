<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 05/03/20
 * Time: 15:09
 */

namespace Oscar\Formatter\Spent;


use Oscar\Exception\OscarException;
use Oscar\Formatter\CSVDownloader;
use Oscar\Formatter\IFormatter;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplateMapResolver;

class EstimatedSpentActivityHTMLFormater implements IFormatter
{
    /** @var array  */
    protected $datas;

    /** @var PhpRenderer */
    private $viewRenderer;

    /** @var string */
    private $templatePath;

    /**
     * SpentActivityExcelFormater constructor.
     * @param $datas
     */
    public function __construct($templatePath, PhpRenderer $viewRenderer, array $datas)
    {
        $this->datas = $datas;
        $this->templatePath = $templatePath;
        $this->viewRenderer = $viewRenderer;
    }


    public function format($options = [])
    {
        if( !file_exists($this->templatePath) ){
            throw new OscarException("Le gabarit pour générer les dépenses prévisionnelles n'existe pas : $this->templatePath");
        }

        try {
            $resolver = new AggregateResolver();
            $map = new TemplateMapResolver([
                'estimated_spent_activity' => $this->templatePath
            ]);
            $this->viewRenderer->setResolver($resolver);
            $resolver->attach($map);
        } catch (\Exception $e) {
            throw new OscarException('Impossible de charger le template');
        }

        $view = new ViewModel($this->datas);
        $view->setTemplate('estimated_spent_activity');
        $view->setTerminal(true);

        try {
            return $this->viewRenderer->render($view);
        } catch (\Exception $e) {
            throw new OscarException("Impossible de générer la vue : " . $e->getMessage());
        }

    }
}