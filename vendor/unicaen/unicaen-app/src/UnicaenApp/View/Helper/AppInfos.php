<?php
namespace UnicaenApp\View\Helper;

use Traversable;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\View\Helper\AppInfos;
use Zend\Config\Config;
use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use Zend\Validator\EmailAddress;

/**
 * Aide de vue affichant les infos sur l'application (nom, description, version, etc.)
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class AppInfos extends AbstractTranslatorHelper
{
    /**
     * @var Config|Traversable|array
     */
    protected $config;

    /**
     * @var bool
     */
    protected $includeContact = false;

    /**
     * @var bool
     */
    protected $htmlListFormat = true;

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
     * Retourne le code HTML affichant les infos sur l'application.
     * 
     * @return string
     */
    public function __toString()
    {
        $appInfos = array();

        // nom de l'appli
        $appInfos[] = $this->getConfig()->get('nom');

        // description de l'appli
        $appInfos[] = $this->getTranslator()->translate($this->getConfig()->get('desc'), $this->getTranslatorTextDomain());

        // version et date de l'appli
        $appVersion = $this->getConfig()->get('version');
        $appDate = $this->getConfig()->get('date');
        $appInfos[] = $this->getTranslator()->translate("Version", $this->getTranslatorTextDomain()) . " " . 
                implode(' ' . $this->getTranslator()->translate("du", $this->getTranslatorTextDomain()) . ' ', array($appVersion, $appDate));

        // mails et/ou téléphones de contact
        if ($this->getIncludeContact()) {
            $parts = $this->extractFormattedContact($this->getConfig()->get('contact'));
            $glue = ' ' . $this->getTranslator()->translate("ou", $this->getTranslatorTextDomain()) . ' ';
            $appInfos[] = $this->getTranslator()->translate("Contact", $this->getTranslatorTextDomain()) . ': ' . implode($glue, $parts);
        }
            
        // assemblage final
        if ($this->getHtmlListFormat()) {
            $htmlList = $this->getView()->plugin('htmlList');
            $out = $htmlList($appInfos, false, array(), false);
        }
        else {
            $out = implode(" | ", $appInfos);
        }
        return $out;
    }

    /**
     * 
     * @param string|array|Config $contact
     * @return array
     */
    protected function extractFormattedContact($contact)
    {
        if (!$contact) {
            return array();
        }
        if (is_string($contact)) {
            if ($this->getHtmlListFormat()) {
                $validator = new EmailAddress();
                if ($validator->isValid($contact)) {
                    return array(
                        sprintf('<a href="mailto:%s" title="%s">%s</a>', 
                                $contact, 
                                $this->getTranslator()->translate("Contacter par mail", $this->getTranslatorTextDomain()), 
                                $contact)
                    );
                }
            }
            return array($contact);
        }
        if ($contact instanceof Config) {
            $contact = $contact->toArray();
        }
        $parts = array();
        foreach ((array)$contact as $c) {
            $parts = array_merge($parts, $this->extractFormattedContact($c));
        }
        return $parts;
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
    public function getConfig()
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
    protected function prepareConfig($config)
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
     * Get contact inclusion flag.
     * 
     * @return bool
     */
    public function getIncludeContact()
    {
        return $this->includeContact;
    }

    /**
     * Set contact inclusion flag.
     * 
     * @param bool $includeContact
     * @return AppInfos
     */
    public function setIncludeContact($includeContact = true)
    {
        $this->includeContact = $includeContact;
        return $this;
    }

    /**
     * Get html list output flag.
     * 
     * @return bool
     */
    public function getHtmlListFormat()
    {
        return $this->htmlListFormat;
    }

    /**
     * Set html list output flag.
     * 
     * @param bool $htmlListFormat
     * @return AppInfos
     */
    public function setHtmlListFormat($htmlListFormat = true)
    {
        $this->htmlListFormat = $htmlListFormat;
        return $this;
    }

    /**
     * Méthode magique permettant d'accéder à une info de l'application en particulier.
     * Exemple : <code>$this->appinfo()->nom</code> (dans une vue).
     *
     * @param string $name
     * @return mixed Un scalaire, une instance de la classe \Zend\Config\Config, ou null
     */
    public function __get($name)
    {
        return $this->getConfig()->get($name);
    }

}
