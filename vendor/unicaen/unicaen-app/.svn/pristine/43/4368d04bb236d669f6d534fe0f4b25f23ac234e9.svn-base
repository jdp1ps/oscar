<?php

namespace UnicaenApp\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class HeadScript extends \Zend\View\Helper\InlineScript implements ServiceLocatorAwareInterface {
    use ServiceLocatorAwareTrait;

    /**
     * Return InlineScript object
     *
     * Returns InlineScript helper object; optionally, allows specifying a
     * script or script file to include.
     *
     * @param  string $mode      Script or file
     * @param  string $spec      Script/url
     * @param  string $placement Append, prepend, or set
     * @param  array  $attrs     Array of script attributes
     * @param  string $type      Script type and/or array of script attributes
     * @return InlineScript
     */
    public function __invoke(
        $mode = self::FILE,
        $spec = null,
        $placement = 'APPEND',
        array $attrs = array(),
        $type = 'text/javascript'
    ) {
        $this->appendConfigScripts();
        return parent::__invoke($mode, $spec, $placement, $attrs, $type);
    }



    protected function appendConfigScripts()
    {
        $config = $this->getServiceLocator()->getServiceLocator()->get('config');

        $version = isset($config['unicaen-app']['app_infos']['version']) ? '?v='.$config['unicaen-app']['app_infos']['version'] : '';

        $publicFiles = isset( $config['public_files'] ) ? $config['public_files'] : [];
        $jsFiles  = isset($publicFiles['head_scripts' ]) ? $publicFiles['head_scripts' ] : [];

        $basePath = $this->getView()->basePath().'/';

        foreach( $jsFiles as $offset => $jsFile ){
            if (0 === strpos($jsFile,'//') || 0 === strpos($jsFile,'http://') || 0 === strpos($jsFile,'https://')){
                $url = $jsFile;
            }else{
                $url = $basePath.$jsFile.$version;
            }
            $this->offsetSetFile($offset, $url, 'text/javascript');
        }
    }
}