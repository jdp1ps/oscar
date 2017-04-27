<?php
namespace UnicaenApp\Controller\Plugin;

use Common\Constants;
use DateTime;
use Traversable;
use UnicaenApp\Exception\LogicException;
use Zend\Config\Config;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Aide de vue affichant les infos sur l'application (nom, description, version, etc.)
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class AppInfos extends AbstractPlugin
{
    /**
     * @var Config|Traversable|array
     */
    protected $config;

    /**
     * @var array
     */
    protected $validOptions = array(
        'nom',
        'desc',
        'version',
        'date',
        'contact'
    );
    
    /**
     * Constructeur.
     * 
     * @param Config|Traversable|array $config
     */
    public function __construct($config = null)
    {
        if ($config) {
            $this->setConfig($config);
        }
    }

    /**
     * Point d'entrée.
     * 
     * @return AppInfos
     */
    public function __invoke()
    {
        return $this;
    }
    
    /**
     * Spécifie les informations concernant l'application.
     * 
     * @param Config|Traversable|array $config
     * @return AppInfos
     */
    public function setConfig($config)
    {
        $this->config = $this->prepareConfig($config);
        return $this;
    }

    /**
     * Retourne les informations concernant l'application.
     * 
     * @return Config
     */
    private function getConfig()
    {
        if (null === $this->config) {
            $this->config = new Config(array());
        }
        return $this->config;
    }

    /**
     * Vérifie la validité des informations fournies.
     * 
     * @param Config|Traversable|array $config
     * @return Config
     */
    private function prepareConfig($config)
    {
        if ($config instanceof Config) {
            $config = $config->toArray();
        }
        else if (!is_array($config)) {
            throw new LogicException("La configuration spécifiée est invalide.");
        }
        if (!$config) {
            throw new LogicException("La configuration spécifiée est vide.");
        }
        $valid = array();
        foreach ($config as $key => $value) {
            if (in_array($key, $this->validOptions)) {
                $valid[$key] = $value;
            }
        }
        return new Config($valid);
    }

    /**
     * Retourne le nom de l'application.
     * 
     * @return string
     */
    public function getNom()
    {
        return $this->getConfig()->get('nom');
    }

    /**
     * Retourne la version de l'application.
     * 
     * @return string Format X.X.X
     */
    public function getVersion()
    {
        return $this->getConfig()->get('version');
    }

    /**
     * Retourne la date de version de l'application.
     * 
     * @return DateTime
     */
    public function getDate()
    {
        return DateTime::createFromFormat(Constants::DATE_FORMAT, $this->getConfig()->get('date'));
    }
}
