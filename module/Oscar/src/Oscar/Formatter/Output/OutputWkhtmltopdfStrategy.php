<?php

namespace Oscar\Formatter\Output;

use Oscar\Exception\OscarException;

class OutputWkhtmltopdfStrategy
{
    private string $fontPath;

    const ORIENTATION_PORTAIT = 'portrait';
    const ORIENTATION_LANDSCAPE = 'landscape';

    /**
     * HtmlToPdfWkhtmltopdfFormatter constructor.
     * @param $fontPath
     */
    public function __construct($fontPath = "")
    {
        $this->fontPath = $fontPath;
    }

    /**
     * @param string $html
     * @param string|null $filename
     * @param $orientation
     * @return void
     * @throws OscarException
     */
    public function output(string $html, string $filename = null, $orientation = self::ORIENTATION_PORTAIT): void
    {
        if ($filename == null) {
            $filename = "document";
        }

        $html_tmp = '/tmp' . DIRECTORY_SEPARATOR . uniqid('htmltmp_') . '.html';
        $pdf_tmp = '/tmp' . DIRECTORY_SEPARATOR . uniqid('pdftmp_') . '.pdf';

        if (file_put_contents($html_tmp, $html)) {
            $cmd = sprintf(
                'export QT_QPA_FONTDIR=%s && export QT_QPA_PLATFORM=offscreen && wkhtmltopdf -O %s %s %s',
                $this->fontPath,
                $orientation,
                $html_tmp,
                $pdf_tmp
            );
            $done = false;
            if (shell_exec($cmd) !== false) {
                header("Content-type:application/pdf");
                header("Content-Disposition:attachment;filename=$filename");
                readfile("$pdf_tmp");
                $done = true;
            }
            unlink($html_tmp);
            unlink($pdf_tmp);
            if (!$done) {
                throw new OscarException("Impossible de générer le fichier PDF");
            }
        } else {
            throw new OscarException("Impossible de créer le fichier temporaire '$html_tmp'");
        }
        die();
    }
}