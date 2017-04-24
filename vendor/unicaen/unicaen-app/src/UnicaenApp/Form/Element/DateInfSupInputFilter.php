<?php
namespace UnicaenApp\Form\Element;

use DateTime;
use InvalidArgumentException;
use UnicaenApp\Validator\EarlierThan;
use UnicaenApp\Validator\LaterThan;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Date as DateValidator;
use Zend\Validator\NotEmpty;
use Zend\Validator\ValidatorChain;

/**
 * Filtre d'entrée associé à la saisie d'une date inférieure (date de
 * début) et une date supérieure éventuelle (date de fin).
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class DateInfSupInputFilter extends InputFilter
{
    const DATE_FORMAT_HUMAN      = 'jj/mm/aaaa'; //
    const DATE_FORMAT_PHP        = 'd/m/Y';      // doivent être cohérents
    const DATE_FORMAT_JAVASCRIPT = "dd/mm/yy";   //
    
    const TIME_FORMAT_HUMAN      = 'hh:mm'; //
    const TIME_FORMAT_PHP        = 'H:i';   // doivent être cohérents
    const TIME_FORMAT_JAVASCRIPT = "HH:mm"; //
    
    const DATETIME_SEPARATOR     = '  \|  ';
    
    /**
     * @var Input
     */
    protected $inputInf;
    
    /**
     * @var Input
     */
    protected $inputSup;
    
    /**
     * @var string
     */
    protected $datetimeFormat;
    
    /**
     * @var bool
     */
    protected $dateInfRequired = true;
    
    /**
     * @var bool
     */
    protected $dateSupRequired = true;
    
    /**
     * @var DateTime
     */
    protected $dateInfMin;
    
    /**
     * @var DateTime
     */
    protected $dateInfMax;
    
    /**
     * @var DateTime
     */
    protected $dateSupMin;
    
    /**
     * @var DateTime
     */
    protected $dateSupMax;
    
    /**
     * @var EarlierThan
     */
    protected $anteriorityValidator;
    
    /**
     * @var boolean
     */
    protected $initialized = false;

    /**
     * Construteur.
     * 
     * @parm string $datetimeFormat Format de date/heure à utiliser
     */
    public function __construct($datetimeFormat)
    {
        $this->setDatetimeFormat($datetimeFormat);
        
        $this->inputInf = new Input(DateInfSup::DATE_INF_ELEMENT_NAME);
        $this->inputSup = new Input(DateInfSup::DATE_SUP_ELEMENT_NAME);
        
        $this->add($this->inputInf)
             ->add($this->inputSup);
        
        // filtres
        $this->inputInf->getFilterChain()->attachByName('StringTrim');
        $this->inputSup->getFilterChain()->attachByName('StringTrim');
    }

    /**
     * Is the data set valid?
     *
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function isValid()
    {
        $this->init();
        
        // date inf <= date sup
        $this->updateAnterioriryValidator();
        
        return parent::isValid();
    }
    
    /**
     * Ajoute les validateurs nécessaires.
     * 
     * @return self
     */
    public function init()
    {
        if ($this->initialized) {
            return $this;
        }
        
        $dateInfValidatorChain = new ValidatorChain();
        $dateSupValidatorChain = new ValidatorChain();
        
        // dates obligatoires (NB: les 'NotEmpty' doivent être les 1ers validateurs installés)
        if ($this->getDateInfRequired()) {
            $dateInfValidatorChain->addValidator(
                    new NotEmpty(array('messages' => array('isEmpty' => _("La date inférieure est requise")))),
                    true);
        }
        if ($this->getDateSupRequired()) {
            $dateSupValidatorChain->addValidator(
                    new NotEmpty(array('messages' => array('isEmpty' => _("La date supérieure est requise")))),
                    true);
        }
        $this->inputInf->setRequired($this->getDateInfRequired());
        $this->inputSup->setRequired($this->getDateSupRequired());

        // format requis pour les dates
        $dateInfValidatorChain->addValidator(
                new DateValidator(array(
                    'format' => $this->getDatetimeFormat(), 
                    'messages' => array(DateValidator::INVALID_DATE => "La date inférieure spécifiée n'est pas valide"),
                )), 
                true);
        $dateSupValidatorChain->addValidator(
                new DateValidator(array(
                    'format' => $this->getDatetimeFormat(), 
                    'messages' => array(DateValidator::INVALID_DATE => "La date supérieure spécifiée n'est pas valide"),
                )), 
                true);
              
//        // date inf <= date sup
//        if ($this->inputInf->getValue() && $this->inputSup->getValue() && $this->inputInf->isValid()) {
//            $max = $this->inputSup->getValue();
//            if (null === $this->anteriorityValidator) {
//                $this->anteriorityValidator = new EarlierThan(array(
//                        'max' => $max, 
//                        'inclusive' => true,
//                        'format' => $this->getDatetimeFormat())
//                );
//                $dateInfValidatorChain->addValidator($this->anteriorityValidator);
//            }
//            else {
//                $this->anteriorityValidator->setMax($max)
//                                           ->setFormat($this->getDatetimeFormat());
//            }
//        }
        
        // bornes sur les dates
        if (($date = $this->getDateInfMin())) {
            $dateInfValidatorChain->addValidator(
                    new LaterThan(array(
                        'min' => $date,
                        'format' => $this->getDatetimeFormat(),
                        'messages' => array(
                            LaterThan::NOT_GREATER => "La date inférieure spécifiée n'est pas postérieure au '%min%'",
                            LaterThan::NOT_GREATER_INCLUSIVE => "La date inférieure spécifiée n'est pas postérieure ou égale au '%min%'"
                        ))
                    ), 
                    true);
        }
        if (($date = $this->getDateInfMax())) {
            $dateInfValidatorChain->addValidator(
                    new EarlierThan(array(
                        'max' => $date,
                        'format' => $this->getDatetimeFormat(),
                        'messages' => array(
                            EarlierThan::NOT_LESS => "La date inférieure spécifiée n'est pas antérieure au '%max%'",
                            EarlierThan::NOT_LESS_INCLUSIVE => "La date inférieure spécifiée n'est pas antérieure ou égale au '%max%'"
                        ))
                    ), 
                    true);
        }
        if (($date = $this->getDateSupMin())) {
            $dateSupValidatorChain->addValidator(
                    new LaterThan(array(
                        'min' => $date,
                        'format' => $this->getDatetimeFormat(),
                        'messages' => array(
                            LaterThan::NOT_GREATER => "La date supérieure spécifiée n'est pas postérieure au '%min%'",
                            LaterThan::NOT_GREATER_INCLUSIVE => "La date supérieure spécifiée n'est pas postérieure ou égale au '%min%'"
                        ))
                    ), 
                    true);
        }
        if (($date = $this->getDateSupMax())) {
            $dateSupValidatorChain->addValidator(
                    new EarlierThan(array(
                        'max' => $date,
                        'format' => $this->getDatetimeFormat(),
                        'messages' => array(
                            EarlierThan::NOT_LESS => "La date supérieure spécifiée n'est pas antérieure au '%max%'",
                            EarlierThan::NOT_LESS_INCLUSIVE => "La date supérieure spécifiée n'est pas antérieure ou égale au '%max%'"
                        ))
                    ), 
                    true);
        }
        
        $this->inputInf->setValidatorChain($dateInfValidatorChain);
        $this->inputSup->setValidatorChain($dateSupValidatorChain);
        
        $this->initialized = true;
        
        return $this;
    }
    
    /**
     * Ajoute le validateur d'antériorité ou le met à jour (max et format), si besoin.
     * 
     * @return self
     */
    protected function updateAnterioriryValidator()
    {
        if ($this->inputInf->getValue() && $this->inputSup->getValue() && $this->inputInf->isValid()) {
            $max = $this->inputSup->getValue();
            if (null === $this->anteriorityValidator) {
                $this->anteriorityValidator = new EarlierThan(array(
                        'max' => $max, 
                        'inclusive' => true,
                        'format' => $this->getDatetimeFormat())
                );
                $this->inputInf->getValidatorChain()->addValidator($this->anteriorityValidator);
            }
            else {
                $this->anteriorityValidator->setMax($max)
                                           ->setFormat($this->getDatetimeFormat());
            }
        }
        return $this;
    }
    
    /**
     * Retourne le format de date requis.
     * 
     * @return string
     */
    public function getDatetimeFormat()
    {
        return $this->datetimeFormat;
    }
    
    /**
     * Retourne le format de date requis lisible par un humain.
     * 
     * @return string
     */
    public function getDatetimeFormatHuman()
    {
        return str_replace(array('d','m','Y','H','i'), array('jj','mm','aaaa','hh','mm'), $this->getDatetimeFormat());
    }

    /**
     * Spécifie le format de date requis.
     * 
     * @param string $datetimeFormat
     * @return self
     */
    public function setDatetimeFormat($datetimeFormat)
    {
        $this->datetimeFormat = $datetimeFormat;
        $this->initialized = false;
        return $this;
    }

    /**
     * Indique si la saisie de la date inférieure est obligatoire ou non.
     * 
     * @return bool
     */
    public function getDateInfRequired()
    {
        return $this->dateInfRequired;
    }

    /**
     * Spécifie si la saisie de la date inférieure est obligatoire ou non.
     * 
     * @param bool $dateInfRequired
     * @return self
     */
    public function setDateInfRequired($dateInfRequired = true)
    {
        $this->dateInfRequired = $dateInfRequired;
        $this->initialized = false;
        return $this;
    }

    /**
     * Indique si la saisie de la date supérieure est obligatoire ou non.
     * 
     * @return bool
     */
    public function getDateSupRequired()
    {
        return $this->dateSupRequired;
    }

    /**
     * Spécifie si la saisie de la date supérieure est obligatoire ou non.
     * 
     * @param bool $required
     * @return self
     */
    public function setDateSupRequired($required = true)
    {
        $this->dateSupRequired = $required;
        $this->initialized = false;
        return $this;
    }

    /**
     * Spécifie la valeur minimum que peut prendre la date inférieure.
     * 
     * @param DateTime|string $date
     * @return self
     */
    public function setDateInfMin($date = null)
    {
        $this->dateInfMin = $this->normalizeDate($date);
        $this->initialized = false;
        return $this;
    }

    /**
     * Spécifie la valeur minimum que peut prendre la date supérieure.
     * 
     * @param DateTime|string $date
     * @return self
     */
    public function setDateInfMax($date = null)
    {
        $this->dateInfMax = $this->normalizeDate($date);
        $this->initialized = false;
        return $this;
    }

    /**
     * Spécifie la valeur maximum que peut prendre la date inférieure.
     * 
     * @param DateTime|string $date
     * @return self
     */
    public function setDateSupMin($date = null)
    {
        $this->dateSupMin = $this->normalizeDate($date);
        $this->initialized = false;
        return $this;
    }

    /**
     * Spécifie la valeur maximum que peut prendre la date supérieure.
     * 
     * @param DateTime|string $date
     * @return self
     */
    public function setDateSupMax($date = null)
    {
        $this->dateSupMax = $this->normalizeDate($date);
        $this->initialized = false;
        return $this;
    }

    /**
     * Transforme si besoin la date spécifiée en objet DateTime.
     * 
     * @param string|DateTime $date
     * @return DateTime
     * @throws InvalidArgumentException
     */
    protected function normalizeDate($date)
    {
        if ($date && is_string($date)) {
            // la date fournie doit être au format attendu courant
            if (!($tmp = \DateTime::createFromFormat($this->getDatetimeFormat(), $date))) {
                throw new InvalidArgumentException("La date spécifiée n'est pas au format attendu '{$this->getDatetimeFormat()}'.");
            }
            $date = $tmp;
        }
        return $date ?: null;
    }
    
    /**
     * Retourne la valeur minimum que peut prendre la date inférieure.
     * 
     * @return DateTime
     */
    public function getDateInfMin()
    {
        return $this->dateInfMin;
    }

    /**
     * Retourne la valeur maximum que peut prendre la date inférieure.
     * 
     * @return DateTime
     */
    public function getDateInfMax()
    {
        return $this->dateInfMax;
    }

    /**
     * Retourne la valeur minimum que peut prendre la date supérieure.
     * 
     * @return DateTime
     */
    public function getDateSupMin()
    {
        return $this->dateSupMin;
    }

    /**
     * Retourne la valeur maximum que peut prendre la date supérieure.
     * 
     * @return DateTime
     */
    public function getDateSupMax()
    {
        return $this->dateSupMax;
    }
}