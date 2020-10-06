<?php


namespace Oscar\Formatter\File;


class HtmlToPdfWkhtmltopdfFormatter implements IHtmlToPdfFormatter
{
    private $orientation = 'portrait';

    public function convert($html, $filename = null, $tobrowser = true)
    {
        if ($filename == null)
            $filename = "document";

        $html_tmp = '/tmp' . DIRECTORY_SEPARATOR . uniqid('htmltmp_') . '.html';
        $pdf_tmp = '/tmp' . DIRECTORY_SEPARATOR . uniqid('pdftmp_') . '.pdf';
        file_put_contents($html_tmp, $html);
        $cmd = 'wkhtmltopdf -O ' . $this->orientation . ' ' . $html_tmp . ' ' . $pdf_tmp;
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