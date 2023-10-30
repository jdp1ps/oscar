<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/01/20
 * Time: 11:02
 */

namespace Oscar\Connector;


use Oscar\Connector\DataAccessStrategy\IDataAccessStrategy;
use Oscar\Exception\ConnectorException;
use Oscar\Exception\OscarException;
use Laminas\ServiceManager\ServiceManager;

abstract class AbstractConnectorOscar implements IConnectorOscar
{
    /** @var ServiceManager */
    private $serviceManager;

    /** @var string Emplacement du fichier de configuration */
    private $configPath;

    /** @var string */
    private $shortName;

    /** @var boolean */
    private $isInit = false;

    /** @var array */
    private $config;

    /** @var array */
    private $options;

    /**
     * @return ServiceManager
     */
    public function getServiceManager(): ServiceManager
    {
        return $this->serviceManager;
    }

    /**
     * @return string
     */
    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    /**
     * @param $optionName
     * @param $optionValue
     */
    public function setOption($optionName, $optionValue)
    {
        $this->options[$optionName] = $optionValue;
    }

    /**
     * @param $optionName
     * @param null $defaultValue
     * @return mixed|null
     */
    public function getOption($optionName, $defaultValue = null)
    {
        if (array_key_exists($optionName, $this->options)) {
            return $this->options[$optionName];
        }
        return $defaultValue;
    }

    /**
     * @return mixed|null
     */
    public function getOptionPurge()
    {
        return $this->getOption('purge', false);
    }

    /**
     * @param $boolean
     */
    public function setOptionPurge($boolean)
    {
        return $this->setOption('purge', $boolean);
    }

    public function getName()
    {
        return $this->shortName;
    }

    abstract function execute($force = true);

    /**
     * Initialisation du connector.
     *
     * @param ServiceManager $sm
     * @param string $configPath
     */
    public function init(ServiceManager $sm, string $configPath, string $shortName): void
    {
        $this->serviceManager = $sm;
        $this->configPath = $configPath;
        $this->shortName = $shortName;
        $this->loadParameters($configPath);
        $this->options = [];
        $this->isInit = true;
    }

    /**
     * @param $filepath
     * @throws ConnectorException
     */
    public function loadParameters(string $filepath)
    {
        if (!file_exists($filepath)) {
            throw new ConnectorException(sprintf("Impossible de charger le fichier de configuration '%s'", $filepath));
        }
        $this->config = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($filepath));
    }

    /**
     * @param $key
     * @return mixed
     * @throws OscarException
     */
    public function getParameter(string $key)
    {
        $paths = explode('.', $key);
        $config = $this->config;
        foreach ($paths as $path) {

            if (!isset($config[$path])) {
                throw new OscarException(sprintf("La clef '%s' absente dans le fichier de configuration '%s'.", $key, $this->configPath));
            }
            $config = $config[$path];
        }
        return $config;
    }

    abstract public function getDataAccess() :IDataAccessStrategy;

    public function checkAccess()
    {
        if( $this->configPath == null ){
            throw new \Exception("Pas initialisé !");
        }
        $datas = $this->getDataAccess()->getDataAll();
        if( $datas ){
            return true;
        }
        return false;
    }
}