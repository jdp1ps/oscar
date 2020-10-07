<?php


namespace Oscar\Formatter\File;


class HtmlToPdfWkhtmltopdfFormatter implements IHtmlToPdfFormatter
{
    private $orientation = 'portrait';
    private $fontPath;

    /**
     * HtmlToPdfWkhtmltopdfFormatter constructor.
     * @param $fontPath
     */
    public function __construct($fontPath="")
    {
        $this->fontPath = $fontPath;
    }


    public function convert($html, $filename = null, $tobrowser = true)
    {
        if ($filename == null)
            $filename = "document";

        $html_tmp = '/tmp' . DIRECTORY_SEPARATOR . uniqid('htmltmp_') . '.html';
        $pdf_tmp = '/tmp' . DIRECTORY_SEPARATOR . uniqid('pdftmp_') . '.pdf';
        file_put_contents($html_tmp, $html);
        $cmd = sprintf('export QT_QPA_FONTDIR=%s && export QT_QPA_PLATFORM=offscreen && wkhtmltopdf -O %s %s %s', $this->fontPath, $this->orientation, $html_tmp, $pdf_tmp);
        $result = shell_exec($cmd);
        header("Content-type:application/pdf");
        header("Content-Disposition:attachment;filename=$filename.pdf");
        readfile("$pdf_tmp");
        die();
    }

    public function setOrientation($orientation)
    {
        $this->orientation = $orientation;
        return $this;
    }
}
