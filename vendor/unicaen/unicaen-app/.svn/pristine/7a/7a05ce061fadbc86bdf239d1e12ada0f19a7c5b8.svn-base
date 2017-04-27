<?php
namespace UnicaenApp\Validator;

use DateTime;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\Exception\InvalidArgumentException;
use Zend\Validator\LessThan;

/**
 * Permet de valider qu'une date est antérieure (ou égale) à une autre.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class EarlierThan extends LessThan
{
    /**
     * @var DateTime
     */
    protected $max;
    
    /**
     * @var string
     */
    protected $format = 'd/m/Y';
    
    /**
     * @var bool
     */
    protected $ignoreTime = false;
    
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_LESS           => "La date spécifiée n'est pas antérieure au '%max%'",
        self::NOT_LESS_INCLUSIVE => "La date spécifiée n'est pas antérieure ou égale au '%max%'"
    );
    
    /**
     * Sets validator options
     *
     * @param  array|Traversable $options
     * @throws InvalidArgumentException
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        
        if (is_array($options) && array_key_exists('format', $options)) {
            $this->setFormat($options['format']);
            unset($options['format']);
        }
        
        if (is_array($options) && array_key_exists('ignore_time', $options)) {
            $this->setIgnoreTime($options['ignore_time']);
            unset($options['ignore_time']);
        }
        
        parent::__construct($options);
    }

    /**
     * Sets the value to be validated and clears the messages and errors arrays
     *
     * @param DateTime|string $value
     * @return self
     */
    protected function setValue($value)
    {
        if (is_string($value)) {
            $date = DateTime::createFromFormat($this->getFormat(), $value);
            if (false === $date) {
                throw new InvalidArgumentException("La date spécifiée '{$value}' ne respecte pas le format '{$this->getFormat()}'.");
            }
            $value = $date;
        }
        
        parent::setValue($value);
        
        return $this;
    }

    /**
     * Returns true if and only if $value is earlier than max date, inclusively
     * when the inclusive option is true
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->setValue($value);
        
        $value = clone $this->getValue();
        $max   = clone $this->getMax();
        
        if ($this->getIgnoreTime()) {
            $value->setTime(0, 0, 0);
            $max->setTime(0, 0, 0);
        }
            
        if ($this->inclusive) {
            if ($value > $max) {
                $this->error(self::NOT_LESS_INCLUSIVE);
                return false;
            }
        } else {
            if ($value >= $max) {
                $this->error(self::NOT_LESS);
                return false;
            }
        }

        return true;
    }
    
    /**
     * Constructs and returns a validation failure message with the given message key and value.
     *
     * Returns null if and only if $messageKey does not correspond to an existing template.
     *
     * If a translator is available and a translation exists for $messageKey,
     * the translation will be used.
     *
     * @param  string              $messageKey
     * @param  string|array|object $value
     * @return string
     */
    protected function createMessage($messageKey, $value)
    {
        $valueOrig = $value;
        $maxOrig = $this->getMax();
        
        if ($valueOrig instanceof DateTime) {
            $value = $valueOrig->format($this->getFormat());
            $this->max = $this->getMax()->format($this->getFormat());
        }
            
        $message = parent::createMessage($messageKey, $value);
        
        if ($valueOrig instanceof DateTime) {
            $this->setMax($maxOrig);
        }
        
        return $message;
    }

    /**
     * Sets the max option
     *
     * @param  DateTime|string $max
     * @return self Provides a fluent interface
     */
    public function setMax($max)
    {
        if (is_string($max)) {
            $date = DateTime::createFromFormat($this->getFormat(), $max);
            if (false === $date) {
                throw new InvalidArgumentException("La date maximum spécifiée '{$max}' ne respecte pas le format '{$this->getFormat()}'.");
            }
            $max = $date;
        }
        
        return parent::setMax($max);
    }
    
    /**
     * Retourne le format de date.
     * 
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Spécifie le format de date.
     * 
     * @param string $format
     * @return self
     */
    public function setFormat($format)
    {
        $this->format = $format;
        
        return $this;
    }
    
    /**
     * Les heures/minutes/secondes sont-elles ignorées dans la comparaion ?
     * 
     * @return bool
     */
    public function getIgnoreTime()
    {
        return $this->ignoreTime;
    }

    /**
     * Spécifie si les heures/minutes/secondes doivent être ignorées dans la comparaion
     * 
     * @param bool $ignoreTime
     * @return self
     */
    public function setIgnoreTime($ignoreTime)
    {
        $this->ignoreTime = (bool) $ignoreTime;
        
        return $this;
    }
}