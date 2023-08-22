<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 06/02/2023
 * @copyright Certic (c) 2023
 */

namespace Oscar\Utils;


use Oscar\Exception\OscarException;

class ViteUtils
{
    /** Configuration */
    private $manifest;

    private $basePath;

    private $buildedJs;

    private $buildedCss;

    private $mode;

    public function __construct(string $mode = 'dev', string $pathRoot = 'dist/', string $baseUrl = '/dist/')
    {
        $this->mode = $mode;
        $this->basePath = $pathRoot;
        $this->baseUrl = $baseUrl;
        $this->buildedJs = [];
        $this->buildedCss = [];

        $this->initManifest();
    }

    private function initManifest()
    {
        if ($this->mode == 'prod') {
            $manifest_location = $this->basePath . '/manifest.json';
            FileSystemUtils::getInstance()->checkFileReadable($manifest_location);
            $this->manifest = json_decode(file_get_contents($manifest_location), true);
        }
    }

    public function build($script)
    {
        if (in_array($script, $this->buildedJs)) {
            return;
        }

        $css_import = [];

        if ($this->mode != 'prod') {
            $jsFile = $script;
        } else {
            if (!array_key_exists($script, $this->manifest)) {
                throw new OscarException("Fichier manquant : le script '$script' est absent du manifeste");
            }

            $conf = $this->manifest[$script];

            if (array_key_exists('imports', $conf)) {
                foreach ($conf['imports'] as $import) {
                    $this->build($import);
                }
            }
            if (array_key_exists('css', $conf)) {
                foreach ($conf['css'] as $css) {
                    if (in_array($css, $this->buildedCss)) {
                        continue;
                    }
                    $this->buildedCss[] = $css;
                    $css_import[] = $css;
                }
            }

            $jsFile = $conf['file'];
        }
        $url = $this->baseUrl . '/' . $jsFile;
        foreach ($css_import as $css) {
            echo '<link rel="stylesheet" href="' . $this->baseUrl . '/' . $css . '" />';
        }
        echo '<script type="module" src="' . $url . '"></script>';
    }
}