<?php


namespace Oscar\Formatter\File;


use Dompdf\Dompdf;

class HtmlToPdfDomPDFFormatter implements IHtmlToPdfFormatter
{
    private $orientation = 'portrait';

    /**
     * HtmlToPdfDomPDFFormatter constructor.
     * @param string $orientation
     */
    public function __construct()
    {

    }


    /**
     * @param $html
     * @param null $filename
     * @param bool $tobrowser
     */
    public function convert($html, $filename = null, $tobrowser = true)
    {

        if ($filename == null) {
            $filename = uniqid('document-') . '.pdf';
        }
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $this->orientation);
        $dompdf->render();
        $dompdf->stream($filename);
        return;
    }

    /**
     * @param $orientation
     * @return $this
     */
    public function setOrientation($orientation)
    {
        $this->orientation = $orientation;
        return $this;
    }
}