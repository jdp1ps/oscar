<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-05-11 16:48
 * @copyright Certic (c) 2017
 */

namespace Oscar\Service;


class ConfigurationParser
{
    private $config;

    /**
     * ConfigurationParser constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getConfiguration($key){
        $config = $this->config;
        if( $key ){
            $paths = explode('.', $key);
            foreach ($paths as $path) {
                if( !isset($config[$path]) ){
                    throw new \Exception("Clef $path absente dans la configuration");
                }
                $config = $config[$path];
            }
        }
        return $config;
    }
}