<?php
namespace Oscar\Connector;

use Monolog\Logger;
use Oscar\Connector\Access\ConnectorAccessCurlHttp;
use Oscar\Connector\Access\IConnectorAccess;
use Oscar\Exception\ConnectorException;
use Oscar\Exception\OscarException;
use Symfony\Component\Yaml\Yaml;
use Laminas\ServiceManager\ServiceManager;

abstract class AbstractConnector implements IConnector
{
    /** @var ServiceManager */
    private $serviceManager;

    /** @var array */
    private $options;

    /** @var array Configuration du connecteur (issue du YAML) */
    private $config;

    /** @var string Emplacement de la configuration */
    private $configFilepath;

    /** @var string  */
    private $connectorName;

    /**
     * @return string
     */
    final function getName() {
        return $this->connectorName;
    }

    /**
     * @return string
     */
    abstract function getRemoteID();

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return ServiceManager
     */
    protected function getServiceLocator(){
        return $this->serviceManager;
    }

    protected function getServicemanager(){
        return $this->serviceManager;
    }

    /**
     * @return Logger
     */
    protected function getLogger(){
        return $this->getServiceLocator()->get('Logger');
    }

    /**
     * @param string $optionName
     * @param mixed $optionValue
     */
    public function setOption(string $optionName, $optionValue) :void {
        $this->options[$optionName] = $optionValue;
    }

    public function getOption($optionName, $defaultValue=null){
        if( $this->options && array_key_exists($optionName, $this->options) ){
            return $this->options[$optionName];
        }
        return $defaultValue;
    }

    public function getOptionPurge(){
        return $this->getOption('purge', false);
    }

    public function setOptionPurge( $boolean ){
        return $this->setOption('purge', $boolean);
    }

    /**
     * @return IConnectorAccess
     * @throws \Oscar\Exception\OsarException
     */
    protected function getAccessStrategy(){
        try {
            // Récupération de la stratégie de connection (si précisée)
            $accessStrategy = $this->getParameter('access_strategy');
        } catch (\Exception $e) {
            // Stratégie par défaut
            $accessStrategy = ConnectorAccessCurlHttp::class;
        }

        /** @var IConnectorAccess $access */
        $access = new $accessStrategy($this);

        return $access;
    }

    /**
     * Initialisation du connecteur.
     *
     * @param ServiceManager $sm
     * @param $configFilePath
     */
    public function init( ServiceManager $sm, string $configPath, string $shortName) :void
    {
        $this->serviceManager = $sm;
        $this->loadParameters($configPath);
        $this->connectorName = $shortName;
    }

    /**
     * Chargement des paramètres des connecteurs depuis le fichier YAML.
     *
     * @param $filepath
     * @throws ConnectorException
     */
    private function loadParameters( string $filepath ){
        if( !file_exists($filepath) ){
            throw new ConnectorException(sprintf("Impossible de charger le fichier de configuration '%s'", $filepath));
        }
        $this->configFilepath = $filepath;
        $this->config = Yaml::parse(file_get_contents($filepath));
    }

    /**
     * Récupération d'un paramètre issu du fichier YAML.
     *
     * @param string $key
     * @return mixed
     * @throws OscarException
     */
    public function getParameter( string  $key, $default = null )
    {
        $paths = explode('.', $key);
        $config = $this->config;
        foreach ($paths as $path) {

            if( !isset($config[$path]) ) {
                if ( $default == null ){
                    throw new OscarException(sprintf(
                        "La clef '%s' absente dans le fichier de configuration '%s'.",
                        $key,
                        $this->configFilepath)
                    );
                } else {
                    return $default;
                }
            }
            $config = $config[$path];
        }
        return $config;
    }

    /**
     * Test la présence d'un paramètre.
     *
     * @param string $key
     * @return bool
     */
    public function hasParameter( string $key ) :bool {
        $paths = explode('.', $key);
        $config = $this->config;
        foreach ($paths as $path) {
            if( !isset($config[$path]) ){
                return false;
            }
        }
        return true;
    }

    public function checkAccess()
    {
        if( $this->config == null ){
            throw new \Exception("Pas initialisé !");
        }
        $datas = $this->getAccessStrategy($this->getPathAll());
        if( $datas ){
            return true;
        }
        return false;
    }
}