<?php


namespace Oscar\Formatter\File;


class HtmlToPdfWkhtmltopdfFormatter implements IHtmlToPdfFormatter
{
    private string $orientation = 'portrait';
    private string $fontPath;

    /**
     * HtmlToPdfWkhtmltopdfFormatter constructor.
     * @param string $fontPath
     */
    public function __construct(string $fontPath = "")
    {
        $this->fontPath = $fontPath;
    }

    /**
     * @param string $html
     * @param string|null $baseFilename
     * @param bool $download
     * @return string|null
     */
    public function convert(string $html, string $baseFilename = null, bool $download = true): ?string
    {
        if ($baseFilename == null) {
            $baseFilename = "document";
        }

        $html_tmp = '/tmp' . DIRECTORY_SEPARATOR . uniqid('html_tmp_') . '.html';
        $pdf_tmp = '/tmp' . DIRECTORY_SEPARATOR . uniqid('pdf_tmp_') . '.pdf';
        file_put_contents($html_tmp, $html);
        $cmd = sprintf(
            'export QT_QPA_FONTDIR=%s && export QT_QPA_PLATFORM=offscreen && wkhtmltopdf -O %s %s %s',
            $this->fontPath,
            $this->orientation,
            $html_tmp,
            $pdf_tmp
        );
        $result = shell_exec($cmd);
        if( $download ) {
            header("Content-type:application/pdf");
            header("Content-Disposition:attachment;filename=$baseFilename.pdf");
            readfile("$pdf_tmp");
        } else {
            $content = file_get_contents($pdf_tmp);
        }

        @unlink($html_tmp);
        @unlink($pdf_tmp);

        if( $download ){
            die();
        } else {
            return $content;
        }
    }

    public function setOrientation($orientation): void
    {
        $this->orientation = $orientation;
    }
}
