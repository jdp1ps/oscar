<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 27/01/16 16:15
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dates des Activités (Jalons)
 *
 * @ORM\Entity
 */
class SpentTypeGroup implements ITrackable
{
    use TraitTrackable;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $label;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\Column(type="string")
     */
    private $code;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="SpentTypeGroup")
     */
    private $parent;


    private $spentTypes;

    /**
     * SpentTypeGroup constructor.
     * @param string $label
     */
    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getSpentTypes()
    {
        return $this->spentTypes;
    }

    /**
     * @param mixed $spentTypes
     */
    public function setSpentTypes($spentTypes)
    {
        $this->spentTypes = $spentTypes;
    }

    public function __toString()
    {
        return $this->getLabel();
    }
}