<?php

namespace UnicaenApp\View\Helper\TabAjax;

use Zend\Stdlib\Hydrator\ClassMethods;

class Tab
{
    /**
     * @var ClassMethods
     */
    private $hydrator;

    /**
     * Identifiant de l'onglet
     *
     * @var string
     */
    protected $id;

    /**
     * URL de chargement AJAX du contenu
     *
     * @var string
     */
    protected $url;

    /**
     * @var boolean
     */
    protected $forceRefresh = false;

    /**
     * Contenu de l'onglet (si URL non transmise)
     *
     * @var string
     */
    protected $content;

    /**
     * Détermine si le contenu est chargé ou non
     *
     * @var boolean
     */
    protected $isLoaded;

    /**
     * Etiquette de l'onglet
     *
     * @var string
     */
    protected $label;

    /**
     * Titre de l'onglet
     *
     * @var string
     */
    protected $title;

    /**
     * Classes liées à l'onglet
     *
     * @var string|array
     */
    protected $class;



    /**
     * Crée un noubel onglet à partir des options transmises
     *
     * @param array $options
     *
     * @return Tab
     */
    static public function createFromOptions($options)
    {
        $tab = new self($options);

        return $tab;
    }



    /**
     * Constructeur
     *
     * @param null|array $options
     */
    public function __construct($options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }
    }



    /**
     * Retourne les paramètres de l'onglet
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->getHydrator()->extract($this);
    }



    /**
     *
     * @param $options
     *
     * @return object
     */
    public function setOptions($options)
    {
        if (isset($options['force-refresh'])){
            $options['forceRefresh'] = $options['force-refresh'];
            unset($options['force-refresh']);
        }
        return $this->getHydrator()->hydrate($options, $this);
    }



    private function getHydrator()
    {
        if (!$this->hydrator) {
            $this->hydrator = new ClassMethods();
        }

        return $this->hydrator;
    }



    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * @param string $id
     *
     * @return Tab
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }



    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }



    /**
     * @param string $url
     *
     * @return Tab
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }



    /**
     * @return boolean
     */
    public function getForceRefresh()
    {
        return $this->forceRefresh;
    }



    /**
     * @param boolean $forceRefresh
     *
     * @return Tab
     */
    public function setForceRefresh($forceRefresh)
    {
        $this->forceRefresh = $forceRefresh;

        return $this;
    }



    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }



    /**
     * @param string $content
     *
     * @return Tab
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }



    /**
     * @return boolean
     */
    public function getIsLoaded()
    {
        return $this->isLoaded;
    }



    /**
     * @param boolean $isLoaded
     *
     * @return Tab
     */
    public function setIsLoaded($isLoaded)
    {
        $this->isLoaded = $isLoaded;

        return $this;
    }



    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }



    /**
     * @param string $label
     *
     * @return Tab
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }



    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }



    /**
     * @param string $title
     *
     * @return Tab
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }



    /**
     * @return array|string
     */
    public function getClass()
    {
        return $this->class;
    }



    /**
     * @param array|string $class
     *
     * @return Tab
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

}