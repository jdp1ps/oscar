<?php

namespace Oscar\Formatter\Output;

use Oscar\Exception\OscarException;

class OutputHtmlStrategy
{
    public function __construct()
    {
    }

    /**
     * @param string $html
     * @param string|null $filename
     * @param $orientation
     * @return void
     * @throws OscarException
     */
    public function output(string $html, string $filename = null): void
    {
        if ($filename == null) {
            $filename = "document";
        }

        $html_tmp = '/tmp' . DIRECTORY_SEPARATOR . uniqid('htmltmp_') . '.html';

        if (file_put_contents($html_tmp, $html)) {
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"$filename\"");
            readfile("$html_tmp");
            unlink($html_tmp);
        } else {
            throw new OscarException("Impossible de créer le fichier temporaire '$html_tmp'");
        }
        die();
    }
}