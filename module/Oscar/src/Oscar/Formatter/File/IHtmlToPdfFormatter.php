<?php


namespace Oscar\Formatter\File;


interface IHtmlToPdfFormatter
{
    const ORIENTATION_LANDSCAPE = 'landscape';
    const ORIENTATION_PORTRAIT = 'portrait';

    public function convert($html, $filename=null, $tobrowser=true);

    /**
     * @param $orientation
     * @return IHtmlToPdfFormatter
     */
    public function setOrientation($orientation);

}