<?php
namespace UnicaenApp\Controller;

use Zend\Http\Header\CacheControl;
use Zend\Http\Header\ContentType;
use Zend\Http\Header\Expires;
use Zend\Http\Header\Pragma;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class CacheController extends AbstractActionController
{

    public function jsAction()
    {
        $content = '';
        $files   = $this->getJsFiles();
        foreach ($files as $file) {
            $content .= file_get_contents($file) . "\n\n\n";
        }

        if (class_exists('JShrink\Minifier')) {
            $content = \JShrink\Minifier::minify($content);
        }

        return $this->getResponse()->setContent($content);
    }



    public function cssAction()
    {
        $content = '';
        $files   = $this->getCssFiles();
        foreach ($files as $file) {
            $content .= file_get_contents($file) . "\n\n\n";
        }

        // Remove comments
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
        // Remove space after colons
        $content = str_replace(': ', ':', $content);
        // Remove whitespace
        $content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $content);

        return $this->getResponse()->setContent($content);
    }



    /**
     * Get response object
     *
     * @return Response
     */
    public function getResponse()
    {
        $response = parent::getResponse();
        /* @var $response \Zend\Http\PhpEnvironment\Response */
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaders([
            ContentType::fromString('Content-Type: text/css'),
            CacheControl::fromString('Cache-Control: public'),
            Expires::fromTimeString('9999-12-31'),
            Pragma::fromString('Pragma: public'),
        ]);
        return $response;
    }



    protected function getPublicDir()
    {
        return getcwd() . '/public';
    }



    protected function getJsFiles()
    {
        $files = [];

        $config = $this->getServiceLocator()->get('config');

        $publicFiles = isset($config['public_files']) ? $config['public_files'] : [];
        $jsFiles     = isset($publicFiles['inline_scripts']) ? $publicFiles['inline_scripts'] : [];
        arsort($jsFiles);
        foreach ($jsFiles as $offset => $jsFile) {
            if (!(0 === strpos($jsFile,'//') || 0 === strpos($jsFile,'http://') || 0 === strpos($jsFile,'https://'))) {
                $files[] = $this->getPublicDir() . '/' . $jsFile;
            }
        }

        return $files;
    }



    protected function getCssFiles()
    {
        $files = [];

        $config = $this->getServiceLocator()->get('config');

        $publicFiles = isset($config['public_files']) ? $config['public_files'] : [];
        $cssFiles    = isset($publicFiles['stylesheets']) ? $publicFiles['stylesheets'] : [];
        arsort($cssFiles);
        foreach ($cssFiles as $offset => $cssFile) {
            if (!(0 === strpos($cssFile,'//') || 0 === strpos($cssFile,'http://') || 0 === strpos($cssFile,'https://'))) {
                $files[] = $this->getPublicDir() . '/' . $cssFile;
            }
        }

        return $files;
    }
}