<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 23/02/16 14:56
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserAccessDefinition
 * @package Oscar\Entity
 * @ORM\Entity()
 */
class UserAccessDefinition
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Contexte technique (nom de classe complet tel que récupéré via get_class($instance)
     *
     * @ORM\Column(type="string", length=200)
     */
    private $context;

    /**
     * @var
     * @ORM\Column(type="string", length=200)
     */
    private $label;

    /**
     * @var
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $description;

    /**
     * @var
     * @ORM\Column(type="string", length=200, unique=true )
     */
    private $key;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return mixed
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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    function __toString()
    {
        return $this->getLabel();
    }


}