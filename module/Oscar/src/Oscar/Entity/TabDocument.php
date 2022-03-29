<?php

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Class TabDocument
 * @package Oscar\Entity
 * @ORM\Entity
 */
class TabDocument
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $label;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $description;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    function __toString()
    {
        return $this->getLabel();
    }


    function toJson()
    {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
        ];
    }


    function toArray()
    {
        return array(
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
        );
    }
}
