<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 03/11/15 14:47
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Currency
 * @package Oscar\Entity
 * @Entity
 */
class Currency
{
    use TraitTrackable;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $label;

    /**
     * @var string
     * @ORM\Column(type="string", length=4)
     */
    private $symbol;

    /**
     * @var double
     * @ORM\Column(type="float")
     */
    private $rate = 1.0;

    public function asArray()
    {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'symbol' => $this->getSymbol(),
            'rate' => $this->getRate(),
        ];
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
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param float $rate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    function __toString()
    {
        return $this->getLabel();
    }
}