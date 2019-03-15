<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 22/02/19
 * Time: 11:26
 */

namespace Oscar\Service;


use Oscar\Exception\OscarException;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class OscarConfigurationService implements ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    protected function getConfig(){
        return $this->getServiceLocator()->get('Config')['oscar'];
    }

    /**
     * @param $key
     * @return array|object
     * @throws OscarException
     */
    public function getConfiguration($key){
        $config = $this->getConfig();
        if( $key ){
            $paths = explode('.', $key);
            foreach ($paths as $path) {
                if( !isset($config[$path]) ){
                    throw new OscarException("Clef '$path' absente dans la configuration");
                }
                $config = $config[$path];
            }
        }
        return $config;
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @return array|null|object
     */
    public function getOptionalConfiguration($key, $defaultValue = null){
        $config = $this->getConfig();
        if( $key ){
            $paths = explode('.', $key);
            foreach ($paths as $path) {
                if( !isset($config[$path]) ){
                    return $defaultValue;
                }
                $config = $config[$path];
            }
        }
        return $config;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return string[]
     */
    public function getNumerotationKeys(){
        return $this->getOptionalConfiguration('editable.numerotation', []);
    }

    protected function getYamlConfigPath(){
        $dir = realpath(__DIR__.'/../../../../../config/autoload/');
        $file = $dir.'/oscar-editable.yml';

        if( !file_exists($file) ){
            if( !is_writeable($dir) ){
                throw new OscarException("Impossible d'écrire la configuration dans le dossier $dir");
            }
        }
        else if (!is_writeable($file)) {
            throw new OscarException("Impossible d'écrire le fichier $file");
        }
        return $file;
    }

    protected function getEditableConfRoot(){
        $path = $this->getYamlConfigPath();
        if( file_exists($path) ){
            $parser = new Parser();
            return $parser->parse(file_get_contents($path));
        } else {
            return [];
        }
    }

    public function saveEditableConfKey($key, $value){
        $conf = $this->getEditableConfRoot();
        $conf[$key] = $value;
        $writer = new Dumper();
        file_put_contents($this->getYamlConfigPath(), $writer->dump($conf));
    }

    public function getEditableConfKey($key, $default = null){
        $conf = $this->getEditableConfRoot();
        if( array_key_exists($key, $conf) ){
            return $conf[$key];
        } else {
            return $default;
        }
    }
}