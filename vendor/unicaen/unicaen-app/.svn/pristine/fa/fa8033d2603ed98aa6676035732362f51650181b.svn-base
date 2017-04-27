<?php
namespace UnicaenApp\Validator;

use DateTime;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\Exception\InvalidArgumentException;
use Zend\Validator\GreaterThan;

/**
 * Permet de valider qu'une date est postérieure (ou égale) à une autre.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class LaterThan extends GreaterThan
{
    /**
     * @var DateTime
     */
    protected $min;
    
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
        self::NOT_GREATER           => "La date spécifiée n'est pas postérieure au '%min%'",
        self::NOT_GREATER_INCLUSIVE => "La date spécifiée n'est pas postérieure ou égale au '%min%'"
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
     * Returns true if and only if $value is later than min date, inclusively
     * when the inclusive option is true
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->setValue($value);
        
        $value = clone $this->getValue();
        $min   = clone $this->getMin();
        
        if ($this->getIgnoreTime()) {
            $value->setTime(0, 0, 0);
            $min->setTime(0, 0, 0);
        }
            
        if ($this->inclusive) {
            if ($value < $min) {
                $this->error(self::NOT_GREATER_INCLUSIVE);
                return false;
            }
        } else {
            if ($value <= $min) {
                $this->error(self::NOT_GREATER);
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
        $minOrig = $this->getMin();
        
        if ($valueOrig instanceof DateTime) {
            $value = $valueOrig->format($this->getFormat());
            $this->min = $this->getMin()->format($this->getFormat());
        }
            
        $message = parent::createMessage($messageKey, $value);
        
        if ($valueOrig instanceof DateTime) {
            $this->setMin($minOrig);
        }
        
        return $message;
    }

    /**
     * Sets the min option
     *
     * @param  DateTime|string $min
     * @return self Provides a fluent interface
     */
    public function setMin($min)
    {
        if (is_string($min)) {
            $date = DateTime::createFromFormat($this->getFormat(), $min);
            if (false === $date) {
                throw new InvalidArgumentException("La date minimum spécifiée '{$min}' ne respecte pas le format '{$this->getFormat()}'.");
            }
            $min = $date;
        }
        
        return parent::setMin($min);
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