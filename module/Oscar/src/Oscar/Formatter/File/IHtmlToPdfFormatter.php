<?php


namespace Oscar\Formatter\File;


interface IHtmlToPdfFormatter
{
    const ORIENTATION_LANDSCAPE = 'landscape';
    const ORIENTATION_PORTRAIT = 'portrait';

    public function convert(string $html, string $baseFilename, bool $download = true): ?string;

    /**
     * @param $orientation
     * @return IHtmlToPdfFormatter
     */
    public function setOrientation(string $orientation): void;

}