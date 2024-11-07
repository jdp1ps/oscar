<?php

namespace Oscar\Service;

use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplateMapResolver;
use Oscar\Exception\OscarException;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;

class DocumentFormatterService implements UseLoggerService, UseOscarConfigurationService
{
    use UseLoggerServiceTrait, UseOscarConfigurationServiceTrait;

    private $viewRenderer;

    /**
     * @return mixed
     */
    public function getViewRenderer(): PhpRenderer
    {
        return $this->viewRenderer;
    }

    /**
     * @param mixed $viewRenderer
     */
    public function setViewRenderer($viewRenderer): self
    {
        $this->viewRenderer = $viewRenderer;
        return $this;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////// CONSTANTS

    const FORMAT_HTML = 'html';
    const FORMAT_PDF = 'pdf';

    const PDF_ORIENTATION_LANDSCAPE = 'landscape';
    const PDF_ORIENTATION_PORTRAIT = 'portrait';

    //////////////////////////////////////////////////////////////////////////////////////////////////////////// LOGGING

    const LOG_MESSAGE = '[DocumentFormatterService] %s';

    protected function debug(string $msg): void
    {
        $this->getLoggerService()->debug(sprintf(self::LOG_MESSAGE, $msg));
    }

    protected function infos(string $msg): void
    {
        $this->getLoggerService()->info(sprintf(self::LOG_MESSAGE, $msg));
    }

    protected function warning(string $msg): void
    {
        $this->getLoggerService()->warning(sprintf(self::LOG_MESSAGE, $msg));
    }

    protected function error(string $msg): void
    {
        $this->getLoggerService()->error(sprintf(self::LOG_MESSAGE, $msg));
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function buildAndDownload(
        string $templatePath,
        array $datas,
        string $format = self::FORMAT_HTML,
        string $baseFilename = "document-oscar",
        string $orientation = self::PDF_ORIENTATION_PORTRAIT
    ): void {
        $html = $this->buildHtmlWithTemplate($templatePath, $datas);
        $this->downloadOutput($html, $format, $baseFilename, $orientation);
    }

    protected function downloadOutput(string $output, string $format, string $baseFilename, string $orientation): void
    {
        if (!in_array($format, [self::FORMAT_PDF, self::FORMAT_HTML])) {
            throw new OscarException("Format '$format' non pris en charge.");
        }

        $extension = $format;

        if ($format == self::FORMAT_HTML) {
            $html_tmp = '/tmp' . DIRECTORY_SEPARATOR . uniqid('oscar-html-tmp-') . '.html';
            file_put_contents($html_tmp, $output);
            header("Content-type:text/html");
            header("Content-Disposition:attachment;filename=$baseFilename.$extension");
            readfile("$html_tmp");
            @unlink($html_tmp);
            die();
        } else {
            try {
                $this->debug("Génération du PDF '$baseFilename.pdf' ($orientation)");
                $pdfMethod = $this->getOscarConfigurationService()->getHtmlToPdfMethod();
                $pdfMethod->setOrientation($orientation);
                $pdfMethod->convert($output, $baseFilename, true);
            } catch (\Exception $e) {
                throw new OscarException("Impossible de télécharger le PDF : " . $e->getMessage());
            }
        }
        die();
    }

    /**
     * Generation du HTML
     * @param string $templatePath
     * @param array $datas
     * @return string
     * @throws OscarException
     */
    protected function buildHtmlWithTemplate(string $templatePath, array $datas): string
    {
        $this->debug("Build HTML from '$templatePath'");
        if (!file_exists($templatePath)) {
            $this->getLoggerService()->critical("Template introuvable");
            throw new OscarException("Le gabarit n'existe pas");
        }

        try {
            $resolver = new AggregateResolver();
            $map = new TemplateMapResolver([
               'oscar_template_generate_html' => $templatePath,
               'layout/layout' => __DIR__ . '/../../../view/error/render_layout.phtml',
               'error/index' => __DIR__ . '/../../../view/error/render_basic.phtml',
           ]);
            $this->viewRenderer->setResolver($resolver);
            $resolver->attach($map);
        } catch (\Exception $e) {
            throw new OscarException('Impossible de charger le template');
        }
        $view = new ViewModel($datas);
        $view->setTerminal(true);
        $view->setTemplate('oscar_template_generate_html');
//        die("ICI");

        try {
            // TODO Essayé de récupérer l'erreur dans le template
            return $this->getViewRenderer()->render($view, $datas);
        } catch (\Exception $e) {
            throw new OscarException("Impossible de générer la vue");
        }
    }
}