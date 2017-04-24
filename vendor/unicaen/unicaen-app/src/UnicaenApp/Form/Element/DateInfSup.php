<?php
namespace UnicaenApp\Form\Element;

/**
 * Elément de formulaire permettant de choisir une date inférieure (date de
 * début) et une date supérieure éventuelle (date de fin).
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class DateInfSup extends \Zend\Form\Element
{
    const DATE_INF_ELEMENT_NAME  = 'inf';
    const DATE_SUP_ELEMENT_NAME  = 'sup';
    
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
    protected $dateInf;
    
    /**
     * @var \DateTime
     */
    protected $dateInfMin;
    
    /**
     * @var \DateTime
     */
    protected $dateInfMax;

    /**
     * @var \DateTime
     */
    protected $dateSup;
    
    /**
     * @var \DateTime
     */
    protected $dateSupMin;
    
    /**
     * @var \DateTime
     */
    protected $dateSupMax;

    /**
     * @var boolean
     */
    protected $dateInfReadonly = false;

    /**
     * @var boolean
     */
    protected $dateSupReadonly = false;

    /**
     * @var string
     */
    protected $dateInfLabel;

    /**
     * @var string
     */
    protected $dateSupLabel;

    /**
     * @var boolean
     */
    protected $dateSupActivated = true;
    
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
        
        $this->setDateInf(new \DateTime());
        $this->setDateSup(null);
        $this->setDateSupActivated(true);
    }

    /**
     * {@inheritedDoc}
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($options['date_inf_label'])) {
            $this->setDateInfLabel($options['date_inf_label']);
        }
        if (isset($options['date_sup_label'])) {
            $this->setDateSupLabel($options['date_sup_label']);
        }
        if (isset($options['date_sup_activated'])) {
            $this->setDateSupActivated($options['date_sup_activated']);
        }

        return $this;
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
     * Retourne la date inférieure courante.
     * 
     * @return \DateTime
     */
    public function getDateInf()
    {
        return $this->dateInf;
    }

    /**
     * Retourne la date inférieure courante au format littéral.
     * 
     * @return string
     */
    public function getDateInfToString()
    {
        if (!$this->dateInf) {
            return '';
        }
        return $this->dateInf->format($this->getDatetimeFormat());
    }

    /**
     * Spécifie si l'heure doit être prise en charge.
     * 
     * @param bool $includeTime
     * @return \UnicaenApp\Form\Element\DateInfSup
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
     * @return \UnicaenApp\Form\Element\DateInfSup
     */
    public function setDateTimeSeparator($sep = null)
    {
        $this->dateTimeSeparator = $sep;
        return $this;
    }

    /**
     * Spécifie la date inférieure courante.
     * 
     * @param string|\DateTime $date
     * @return \UnicaenApp\Form\Element\DateInfSup
     */
    public function setDateInf($date)
    {
        $this->dateInf = $this->normalizeDate($date);
        return $this;
    }

    /**
     * Retourne la date supérieure courante.
     * 
     * @return \DateTime
     */
    public function getDateSup()
    {
        return $this->dateSup;
    }

    /**
     * Retourne la date supérieure courante au format littéral.
     * 
     * @return string
     */
    public function getDateSupToString()
    {
        if (!$this->dateSup) {
            return '';
        }
        return $this->dateSup->format($this->getDatetimeFormat());
    }

    /**
     * Spécifie la date supérieure courante.
     * 
     * @param string|\DateTime $date
     * @return \UnicaenApp\Form\Element\DateInfSup
     */
    public function setDateSup($date)
    {
        $this->dateSup = $this->normalizeDate($date);
        return $this;
    }

    /**
     * Spécifie la valeur minimum de la date inférieure.
     * NB: l'heure n'est pas prise en compte.
     * 
     * @param string|\DateTime $date
     * @return \UnicaenApp\Form\Element\DateInfSup
     */
    public function setDateInfMin($date = null)
    {
        $this->dateInfMin = $this->normalizeDate($date);
        return $this;
    }

    /**
     * Spécifie la valeur maximum de la date inférieure.
     * NB: l'heure n'est pas prise en compte.
     * 
     * @param string|\DateTime $date
     * @return \UnicaenApp\Form\Element\DateInfSup
     */
    public function setDateInfMax($date = null)
    {
        $this->dateInfMax = $this->normalizeDate($date);
        return $this;
    }

    /**
     * Spécifie la valeur minimum de la date supérieure.
     * NB: l'heure n'est pas prise en compte.
     * 
     * @param string|\DateTime $date
     * @return \UnicaenApp\Form\Element\DateInfSup
     */
    public function setDateSupMin($date)
    {
        $this->dateSupMin = $this->normalizeDate($date);
        return $this;
    }

    /**
     * Spécifie la valeur maximum de la date supérieure.
     * NB: l'heure n'est pas prise en compte.
     * 
     * @param string|\DateTime $date
     * @return \UnicaenApp\Form\Element\DateInfSup
     */
    public function setDateSupMax($date = null)
    {
        $this->dateSupMax = $this->normalizeDate($date);
        return $this;
    }

    /**
     * Transforme si besoin la date spécifiée en objet DateTime.
     * 
     * @param string|\DateTime $date
     * @return \DateTime
     * @throws \InvalidArgumentException
     */
    protected function normalizeDate($date)
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
    public function getDateInfMin()
    {
        return $this->dateInfMin;
    }

    /**
     * Retourne la valeur maximum éventuelle de la date inférieure.
     * 
     * @return \DateTime
     */
    public function getDateInfMax()
    {
        return $this->dateInfMax;
    }

    /**
     * Retourne la valeur minimum éventuelle de la date supérieure.
     * 
     * @return \DateTime
     */
    public function getDateSupMin()
    {
        return $this->dateSupMin;
    }

    /**
     * Retourne la valeur maximum éventuelle de la date supérieure.
     * 
     * @return \DateTime
     */
    public function getDateSupMax()
    {
        return $this->dateSupMax;
    }

    /**
     * Indique si la saisie de la date supérieure est activée ou non.
     * 
     * @return bool
     */
    public function getDateSupActivated()
    {
        return $this->dateSupActivated;
    }

    /**
     * Spécifie si la saisie de la date supérieure est activée ou non.
     * 
     * @param bool $dateSupActivated
     * @return \UnicaenApp\Form\Element\DateInfSup
     */
    public function setDateSupActivated($dateSupActivated = true)
    {
        $this->dateSupActivated = $dateSupActivated;
        return $this;
    }

    /**
     * Retourne le libellé du champ de saisie de la date inférieure.
     * 
     * @return string
     */
    public function getDateInfLabel()
    {
        if (null === $this->dateInfLabel) {
            $this->dateInfLabel = $this->getIncludeTime() ? "Date et heure de début" : "Date de début";
        }
        return $this->dateInfLabel;
    }

    /**
     * Spécifie le libellé du champ de saisie de la date inférieure.
     * 
     * @param string $dateInfLabel
     * @return \UnicaenApp\Form\Element\DateInfSup
     */
    public function setDateInfLabel($dateInfLabel)
    {
        $this->dateInfLabel = $dateInfLabel;
        return $this;
    }

    /**
     * Retourne le libellé du champ de saisie de la date supérieure.
     * 
     * @return string
     */
    public function getDateSupLabel()
    {
        if (null === $this->dateSupLabel) {
            $this->dateSupLabel = $this->getIncludeTime() ? "Date et heure de fin" : "Date de fin";
        }
        return $this->dateSupLabel;
    }

    /**
     * Spécifie le libellé du champ de saisie de la date supérieure.
     * 
     * @param string $dateSupLabel
     * @return \UnicaenApp\Form\Element\DateInfSup
     */
    public function setDateSupLabel($dateSupLabel)
    {
        $this->dateSupLabel = $dateSupLabel;
        return $this;
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
     * @param  \DateTime|array $value \DateTime ou array('inf'=>\DateTime[, 'sup'=>\DateTime])
     * @return \UnicaenApp\Form\Element\DateInfSup
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            if (1 === count($value)) {
                if (isset($value[self::DATE_INF_ELEMENT_NAME])) {
                    $this->setDateInf(reset($value));
                }
                elseif (isset($value[self::DATE_SUP_ELEMENT_NAME])) {
                    $this->setDateSup(reset($value));
                }
                else {
                    $this->setDateInf(reset($value));
                }
            }
            else {
                if (array_key_exists(self::DATE_INF_ELEMENT_NAME, $value) || array_key_exists(self::DATE_SUP_ELEMENT_NAME, $value)) {
                    $this->setDateInf(isset($value[self::DATE_INF_ELEMENT_NAME]) ? $value[self::DATE_INF_ELEMENT_NAME] : null);
                    $this->setDateSup(isset($value[self::DATE_SUP_ELEMENT_NAME]) ? $value[self::DATE_SUP_ELEMENT_NAME] : null);
                }
                else {
                    $value = array_values($value);
                    $this->setDateInf(reset($value));
                    $this->setDateSup(next($value));
                }
            }
        }
        else {
            $this->setDateInf($value);
        }
        return $this;
    }

    /**
     * Retourne la valeur courante de cet élément.
     * 
     * @return array 'inf'=>\DateTime, 'sup'=>\DateTime
     */
    public function getValue()
    {
        $value = array();
        $value[self::DATE_INF_ELEMENT_NAME] = 
                $this->getDateInf() ? $this->getDateInf()->format($this->getDateFormat()) : null;
        $value[self::DATE_SUP_ELEMENT_NAME] = 
                $this->getDateSupActivated() && $this->getDateSup() ? $this->getDateSup()->format($this->getDateFormat()) : null;
        return $value;
    }

    /**
     * Injecte le filtre d'entrée dédié à cet élément.
     * 
     * @return DateInfSupInputFilter
     */
    public function setInputFilter(DateInfSupInputFilter $inputFilter = null)
    {
        $this->inputFilter = $inputFilter;
        if ($this->inputFilter) {
            $this->inputFilter->setDatetimeFormat($this->getDatetimeFormat());
        }
        return $this;
    }

    /**
     * Retourne le filtre d'entrée dédié à cet élément.
     * 
     * @return DateInfSupInputFilter
     */
    public function getInputFilter()
    {
        if (null === $this->inputFilter) {
            $this->setInputFilter(self::getDefaultInputFilter($this->getDatetimeFormat()));
        }
        return $this->inputFilter;
    }

    /**
     * Set a list of messages to report when validation fails
     *
     * @param  array|Traversable $messages
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setMessages($messages)
    {
        if ($messages && is_array($messages)) {
            if (!array_key_exists('inf', $messages) || !array_key_exists('sup', $messages)) {
                $msg = array();
                if (1 === count($messages)) {
                    if (array_key_exists('inf', $messages) || array_key_exists('sup', $messages)) {
                        if (isset($messages['inf'])) {
                            $msg['inf'] = $messages['inf'];
                        }
                        if (isset($messages['sup'])) {
                            $msg['sup'] = $messages['sup'];
                        }
                    }
                    else {
                        // même message pour la date inférieure et la date supérieure
                        $msg['inf'] = 
                        $msg['sup'] = current($messages);
                    }
                }
                elseif (2 === count($messages)) {
                    // messages différents pour la date inférieure et la date supérieure
                    $msg['inf'] = current($messages);
                    $msg['sup'] = next($messages);
                }
                else {
                    // mêmes messages pour la date inférieure et la date supérieure
                    $msg['inf'] = $messages;
                    $msg['sup'] = $messages;
                }
                $messages = $msg;
            }
        }
        
        return parent::setMessages($messages);
    }

    /**
     * Retourne le filtre d'entrée dédié par défaut.
     * 
     * @parm string $datetimeFormat Format de date/heure à utiliser
     * @return \UnicaenApp\Form\Element\DateInfSupInputFilter
     */
    static public function getDefaultInputFilter($datetimeFormat = null)
    {
        $inputFilter = new DateInfSupInputFilter($datetimeFormat);
        $inputFilter->setDateInfRequired(true)
                    ->setDateSupRequired(true);
        return $inputFilter;
    }
}