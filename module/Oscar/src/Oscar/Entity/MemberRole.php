<?php

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rôle disponible dans un projet.
 * 
 * ORM\Entity
 */
class MemberRole
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private $description;

    public function getId()
    {
        return $this->id;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
    
    public function __toString() 
    {
        return $this->getLabel();
    }
}
