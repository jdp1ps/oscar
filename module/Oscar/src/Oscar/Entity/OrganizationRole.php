<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 12/06/15 12:53
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oscar\Utils\DateTimeUtils;

/**
 * Membre d'un projet.
 * @package Oscar\Entity
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="Oscar\Entity\OrganizationRoleRepository")
 */
class OrganizationRole
{

    use TraitTrackable;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, unique=true, nullable=false)
     */
    protected $label;

    /**
     * Description du rôle.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description = "";

    /**
     * Est un rôle principal (Généralement laboratoire, composante)
     *
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false, options={"default"=false})
     */
    protected $principal = false;

    /**
     * @return int
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

    /**
     * @return boolean
     */
    public function isPrincipal()
    {
        return $this->principal;
    }

    /**
     * @param boolean $principal
     */
    public function setPrincipal($principal)
    {
        $this->principal = $principal;

        return $this;
    }


    function toArray()
    {
        return array(
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
            'principal' => (boolean)$this->isPrincipal(),
        );
    }

    function toJson()
    {
        return $this->toArray();
    }

    function __toString()
    {
        return $this->getLabel();
    }
}
