<?php
namespace UnicaenApp\Form\Element;

/**
 * Elément de formulaire permettant de choisir une date.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Date extends \Zend\Form\Element
{
    const DATE_FORMAT_HUMAN      = DateInfSupInputFilter::DATE_FORMAT_HUMAN;
    const DATE_FORMAT_PHP        = DateInfSupInputFilter::DATE_FORMAT_PHP;
    const DATE_FORMAT_JAVASCRIPT = DateInfSupInputFilter::DATE_FORMAT_JAVASCRIPT;
    
    const TIME_FORMAT_HUMAN      = DateInfSupInputFilter::TIME_FORMAT_HUMAN;
    const TIME_FORMAT_PHP        = DateInfSupInputFilter::TIME_FORMAT_PHP;
    const TIME_FORMAT_JAVASCRIPT = DateInfSupInputFilter::TIME_FORMAT_JAVASCRIPT;
    
    const DATETIME_SEPARATOR     = DateInfSupInputFilter::DATETIME_SEPARATOR;
    
    /**
     * @var bool
     */
    protected $includeTime = false;
    
    /**
     * @var bool
     */
    protected $dateTimeSeparator;
    
    /**
     * @var \DateTime
     */
    protected $date;
    
    /**
     * @var \DateTime
     */
    protected $dateMin;
    
    /**
     * @var \DateTime
     */
    protected $dateMax;

    /**
     * @var boolean
     */
    protected $dateReadonly = false;
    
    /**
     * @var DateInfSupInputFilter 
     */
    protected $inputFilter;

    /**
     * Constructeur.
     * 
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);

        if (array_key_exists('include_time', $options)) {
            $this->setIncludeTime((bool) $options['include_time']);
        }
        
        $this->setDate(new \DateTime());
    }
    
    /**
     * Retourne le format de dates à utiliser, selon que l'heure est prise en charge ou non
     * 
     * @param boolean $forceIncludeTime Forcer l'inclusion de l'heure
     * @return string
     */
    public function getDatetimeFormat($forceIncludeTime = false)
    {
        $format = self::DATE_FORMAT_PHP;
        if ($forceIncludeTime || $this->getIncludeTime()) {
            $format .= $this->getDateTimeSeparator() . self::TIME_FORMAT_PHP;
        }
        return $format;
    }
    
    /**
     * Retourne le format de dates à utiliser, selon que l'heure est prise en charge ou non
     * 
     * @return string
     */
    public function getDatetimeFormatHuman()
    {
        return str_replace(array('d','m','Y','H','i'), array('jj','mm','aaaa','hh','mm'), $this->getDatetimeFormat());
    }
        
    /**
     * Indique si l'heure est prise en charge.
     * 
     * @return bool
     */
    public function getIncludeTime()
    {
        return $this->includeTime;
    }
    
    /**
     * Retourne la chaîne de caractères à afficher entre la date et l'heure.
     * 
     * @return string
     */
    public function getDateTimeSeparator()
    {
        if (null === $this->dateTimeSeparator) {
            $this->dateTimeSeparator = self::DATETIME_SEPARATOR;
        }
        return $this->dateTimeSeparator;
    }

    /**
     * Retourne la date courante.
     * 
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Retourne la date courante au format littéral.
     * 
     * @return string
     */
    public function getDateToString()
    {
        if (!$this->date) {
            return '';
        }
        return $this->date->format($this->getDatetimeFormat());
    }

    /**
     * Spécifie si l'heure doit être prise en charge.
     * 
     * @param bool $includeTime
     * @return self
     */
    public function setIncludeTime($includeTime = true)
    {
        $this->includeTime = (bool) $includeTime;
        return $this;
    }
    
    /**
     * Spécifie la chaîne de caractères à afficher entre la date et l'heure.
     * 
     * @param string $sep Spécifier null pour le séparateur par défaut
     * @return self
     */
    public function setDateTimeSeparator($sep = null)
    {
        $this->dateTimeSeparator = $sep;
        return $this;
    }

    /**
     * Spécifie la date courante.
     * 
     * @param string|\DateTime $date
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $this->normalizeDate($date);
        return $this;
    }

    /**
     * Spécifie la valeur minimum de la date.
     * NB: l'heure n'est pas prise en compte.
     * 
     * @param string|\DateTime $date
     * @return self
     */
    public function setDateMin($date = null)
    {
        $this->dateMin = $this->normalizeDate($date);
        return $this;
    }

    /**
     * Spécifie la valeur maximum de la date.
     * NB: l'heure n'est pas prise en compte.
     * 
     * @param string|\DateTime $date
     * @return self
     */
    public function setDateMax($date = null)
    {
        $this->dateMax = $this->normalizeDate($date);
        return $this;
    }

    /**
     * Transforme si besoin la date spécifiée en objet DateTime.
     * 
     * @param string|\DateTime $date
     * @return \DateTime
     * @throws \InvalidArgumentException
     */
    public function normalizeDate($date)
    {
        if ($date && is_string($date)) {
            // la date fournie doit être au format attendu courant
            if (!($tmp = \DateTime::createFromFormat($this->getDatetimeFormat(), $date))) {
                throw new \InvalidArgumentException("Le format de la date spécifiée est incorrect.");
            }
            $date = $tmp;
            $date->setTime($date->format("H"), $date->format("i"), 0);
        }
        return $date ?: null;
    }

    /**
     * Retourne la valeur minimum éventuelle de la date inférieure.
     * 
     * @return \DateTime
     */
    public function getDateMin()
    {
        return $this->dateMin;
    }

    /**
     * Retourne la valeur maximum éventuelle de la date inférieure.
     * 
     * @return \DateTime
     */
    public function getDateMax()
    {
        return $this->dateMax;
    }

    /**
     * Retourne le format de date PHP ou Javascript requis.
     * 
     * @param bool $javascript Mettre à true pour retourner le format Javascript
     * @return string
     */
    public function getDateFormat($javascript = false)
    {
        return $javascript ? self::DATE_FORMAT_JAVASCRIPT : $this->getDatetimeFormat();
    }

    /**
     * Spécifie la valeur courante de cet élément.
     * 
     * @param  \DateTime $value 
     * @return self
     */
    public function setValue($value)
    {
        $this->setDate($value);
        
        return $this;
    }

    /**
     * Retourne la valeur courante de cet élément.
     * 
     * @return array 'inf'=>\DateTime, 'sup'=>\DateTime
     */
    public function getValue()
    {
        return $this->getDate() ? $this->getDate()->format($this->getDateFormat()) : null;
    }
}