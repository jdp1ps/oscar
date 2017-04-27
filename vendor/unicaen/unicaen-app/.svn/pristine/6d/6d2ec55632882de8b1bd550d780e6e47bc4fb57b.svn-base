<?php

namespace UnicaenApp\Filter;

use NumberFormatter;
use UnicaenApp\Util;
use Zend\Filter\AbstractFilter;
use Zend\Filter\Exception\RuntimeException;

/**
 * Convertit et formatte un nombre d'octets en ko, Mo, Go ou To. 
 * Exemples:
 * 765   --> 765 o
 * 1024  --> 1 ko
 * 79057 --> 77,2 ko
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class BytesFormatter extends AbstractFilter
{    
    /**
     * Returns the result of filtering $value
     *
     * @param  float $value
     * @throws RuntimeException If filtering $value is impossible
     * @return mixed
     */
    public function filter($value)
    {
        $units = array('o', 'ko', 'Mo', 'Go', 'To');

        $result = max($value, 0);
        $pow    = floor(($result ? log($result) : 0) / log(1024));
        $pow    = min($pow, count($units) - 1);

        $result /= pow(1024, $pow);

        $result = Util::formattedFloat($result, NumberFormatter::DECIMAL, $this->getPrecision());
        
        return $result . ' ' . $units[$pow];
    }
    
    /**
     * @var integer
     */
    protected $precision = 1;
    
    public function getPrecision()
    {
        return $this->precision;
    }

    public function setPrecision($precision)
    {
        $this->precision = $precision;
        return $this;
    }
}