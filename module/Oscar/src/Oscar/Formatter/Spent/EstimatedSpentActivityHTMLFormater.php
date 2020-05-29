<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 05/03/20
 * Time: 15:09
 */

namespace Oscar\Formatter\Spent;


use Oscar\Entity\Activity;
use Oscar\Exception\OscarException;
use Oscar\Formatter\CSVDownloader;
use Oscar\Formatter\IFormatter;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;

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

        $view = new ViewModel($this->datas);
        $view->setTemplate('estimated_spent_activity');
        $view->setTerminal(true);

        $resolver = new AggregateResolver();
        $map = new TemplateMapResolver([
            'estimated_spent_activity' => $this->templatePath
        ]);
        $this->viewRenderer->setResolver($resolver);
        $resolver->attach($map);

        return $this->viewRenderer->render($view);

        //
        if( array_key_exists('download', $options) && $options['download'] === true ){
            $downloader = new CSVDownloader();
            $downloader->downloadCSVToExcel($filename);
        }
    }
}