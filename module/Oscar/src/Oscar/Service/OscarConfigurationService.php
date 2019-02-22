<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 22/02/19
 * Time: 11:26
 */

namespace Oscar\Service;


use Oscar\Exception\OscarException;
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
}