<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-05-24 11:47
 * @copyright Certic (c) 2017
 */

namespace Oscar\Connector;

use Oscar\Exception\ConnectorException;
use Oscar\Exception\OscarException;
use Oscar\Service\ConfigurationParser;
use Symfony\Component\Yaml\Yaml;

/**
 * Système de gestion de paramètre pour un connector.
 *
 * Class ConnectorParametersTrait
 * @package Oscar\Connector
 */
trait ConnectorParametersTrait
{
    private $config;
    private $configFilepath;

    /**
     * @param $filepath
     * @throws ConnectorException
     */
    public function loadParameters( $filepath ){
        if( !file_exists($filepath) ){
            throw new ConnectorException(sprintf("Impossible de charger le fichier de configuration '%s'", $filepath));
        }
        $this->configFilepath = $filepath;
        $this->config = Yaml::parse(file_get_contents($filepath));
    }

    /**
     * @param $key
     * @return mixed
     * @throws OscarException
     */
    public function getParameter( $key ){
        $paths = explode('.', $key);
        $config = $this->config;
        foreach ($paths as $path) {

            if( !isset($config[$path]) ){
                throw new OscarException(sprintf("La clef '%s' absente dans le fichier de configuration '%s'.", $key, $this->configFilepath));
            }
            $config = $config[$path];
        }
        return $config;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasParameter( $key ){
        $paths = explode('.', $key);
        $config = $this->config;
        foreach ($paths as $path) {
            if( !isset($config[$path]) ){
                return false;
            }
        }
        return true;
    }
}