<?php

namespace Oscar\Formatter\File;

use Dompdf\Dompdf;
use Oscar\Exception\OscarException;

class HtmlToPdfDomPDFFormatter implements IHtmlToPdfFormatter
{
    private string $orientation = 'portrait';

    /**
     * @param string $html
     * @param string $baseFilename
     * @param bool $download
     * @return string|null
     * @throws OscarException
     */
    public function convert(string $html, string $baseFilename, bool $download = true): ?string
    {
        if ($baseFilename == null) {
            $baseFilename = uniqid('document-');
        }

        $filename = "$baseFilename.pdf";

        try {
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', $this->orientation);
            $dompdf->render();
            $dompdf->stream($filename);
            die();
        } catch (\Exception $e) {
            throw new OscarException($e->getMessage());
        }
    }

    /**
     * @param string $orientation
     * @return void
     */
    public function setOrientation(string $orientation): void
    {
        $this->orientation = $orientation;
    }
}