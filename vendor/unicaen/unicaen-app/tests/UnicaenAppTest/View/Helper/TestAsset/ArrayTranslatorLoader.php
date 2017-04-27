<?php
namespace UnicaenAppTest\View\Helper\TestAsset;

use Zend\I18n\Translator;

class ArrayTranslatorLoader implements Translator\Loader\FileLoaderInterface
{
    public $translations;

    public function load($filename, $locale)
    {
        $textDomain =  new Translator\TextDomain($this->translations);
        return $textDomain;
    }
}