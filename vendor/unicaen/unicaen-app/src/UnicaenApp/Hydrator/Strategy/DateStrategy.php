<?php

namespace UnicaenApp\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use UnicaenApp\Form\Element\Date as DateElement;

/**
 * Description of DateStrategy
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class DateStrategy implements StrategyInterface
{
    /**
     * @var DateElement
     */
    private $element;
    
    /**
     * Constructeur.
     *  
     * @param \UnicaenApp\Form\Element\Date $element Fournit le format de date
     */
    public function __construct(DateElement $element)
    {
        $this->element = $element;
    }
    
    /**
     * Converts the given value so that it can be extracted by the hydrator.
     *
     * @param mixed   $value The original value.
     * @param object $object (optional) The original object for context.
     * @return mixed Returns the value that should be extracted.
     */
    public function extract($value)
    {
        return $value;
    }

    /**
     * Converts the given value so that it can be hydrated by the hydrator.
     *
     * @param mixed $value The original value.
     * @param array  $data (optional) The original data for context.
     * @return \DateTime Returns the value that should be hydrated.
     */
    public function hydrate($value)
    {
        if ($value instanceof \DateTime) {
            return $value;
        }
        return $this->element->normalizeDate($value);
    }
}