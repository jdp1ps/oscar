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
 * Class TVA
 * @package Oscar\Entity
 * @Entity
 */
class TVA
{
    use TraitTrackable;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $label;

    /**
     * @var double
     * @ORM\Column(type="float")
     */
    private $rate = 0.2;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active = true;


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

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }



    function __toString()
    {
        return $this->getLabel();
    }
}