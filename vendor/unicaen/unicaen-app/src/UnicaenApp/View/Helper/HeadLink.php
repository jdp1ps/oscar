<?php

namespace UnicaenApp\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\Placeholder\Container\AbstractContainer;

class HeadLink extends \Zend\View\Helper\HeadLink implements ServiceLocatorAwareInterface {
    use ServiceLocatorAwareTrait;



    /**
     * Return headStyle object
     *
     * Returns headStyle helper object; optionally, allows specifying
     *
     * @param  string       $content    Stylesheet contents
     * @param  string       $placement  Append, prepend, or set
     * @param  string|array $attributes Optional attributes to utilize
     * @return HeadStyle
     */
    public function __invoke(array $attributes = null, $placement = AbstractContainer::APPEND)
    {
        $this->appendConfigStyles();
        return parent::__invoke($attributes, $placement);
    }



    protected function appendConfigStyles()
    {
        $config = $this->getServiceLocator()->getServiceLocator()->get('config');

        $cacheEnabled = isset($config['public_files']['cache_enabled']) ? (boolean)$config['public_files']['cache_enabled'] : false;
        $version = isset($config['unicaen-app']['app_infos']['version']) ? $config['unicaen-app']['app_infos']['version'] : '';

        $publicFiles = isset( $config['public_files'] ) ? $config['public_files'] : [];
        $cssFiles  = isset($publicFiles['stylesheets' ]) ? $publicFiles['stylesheets' ] : [];

        $basePath = $this->getView()->basePath().'/';

        foreach( $cssFiles as $offset => $cssFile ){
            if (0 === strpos($cssFile,'//') || 0 === strpos($cssFile,'http://') || 0 === strpos($cssFile,'https://')){
                $this->offsetSetStylesheet($offset, $cssFile);
            }elseif(!$cacheEnabled) {
                $url = $basePath.$cssFile;
                if ($version) $url .= '?v='.$version;
                $this->offsetSetStylesheet($offset, $url);
            }
        }
        if ($cacheEnabled){
            $this->offsetSetStylesheet(999,$this->getView()->url('cache/css', ['version' => $version]));
        }
    }
}