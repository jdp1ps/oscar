<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 27/01/16 16:15
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Dates des Activités (Jalons)
 * @ORM\Entity(repositoryClass="SpentTypeGroupRepository")
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
     * @ORM\Column(type="integer")
     */
    private $rgt;

    /**
     * @ORM\Column(type="integer")
     */
    private $lft;

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
        $this->spentTypes = new ArrayCollection();
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
        return $this;
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
        return $this;
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
        return $this;
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

    /**
     * @return mixed
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * @param mixed $rgt
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @param integer $lft
     * @return $this
     */
    public function setLft($lft)
    {
        $this->lft = $lft;
        return $this;
    }


    public function toJson(){
        return [
            'id'    => $this->getId(),
            'label' => $this->getLabel(),
            'parent' => $this->getParent() ? $this->getParent()->getId() : null,
            'description' => $this->getDescription(),
            'code' => $this->getCode(),
            'rgt' => $this->getRgt(),
            'lft' => $this->getLft()
        ];
    }

    public function __toString()
    {
        return sprintf("%s (%s, %s)", $this->getLabel(), $this->getRgt(), $this->getLft());
    }


}