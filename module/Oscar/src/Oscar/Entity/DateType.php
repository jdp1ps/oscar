<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/11/15 10:09
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class DateType
 * @package Oscar\Entity
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Oscar\Entity\DateTypeRepository")
 */
class DateType implements ITrackable
{
    use TraitTrackable;

    /**
     * Intitulé du type
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $label;

    /**
     * Permet juste de filtrer les types de dates (OscarFacet)
     *
     * @var
     * @ORM\Column(type="string", nullable=true)
     */
    private $facet;

    /**
     * Description.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $description;

    /**
     * Permet de configurer les féquences de rappel.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $recursivity;

    /**
     * Détermine si le jalon intégre des notions d'accomplissement.
     *
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default"=false})
     */
    private $finishable = false;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="ActivityDate", mappedBy="type")
     */
    private $milestones;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="datesType", fetch="EAGER")
     * @ORM\JoinTable(name="role_datetype")
     *      joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="date_type_id", referencedColumnName="id")})
     */
    protected $roles;


    /**
     * DateType constructor.
     */
    public function __construct()
    {
        $this->milestones = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }


    /**
     * @return bool
     */
    public function isFinishable()
    {
        return $this->finishable;
    }

    /**
     * @param bool $finishable
     */
    public function setFinishable($finishable)
    {
        $this->finishable = $finishable;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getRecursivity()
    {
        return $this->recursivity;
    }

    public function setRecursivity( $value )
    {
        $this->recursivity = $value;
        return $this;
    }

    public function getRecursivityArray()
    {
        if( $this->recursivity ){
            return explode(',', $this->recursivity);
        } else {
            return [];
        }
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
     * @return mixed
     */
    public function getFacet()
    {
        return $this->facet;
    }

    /**
     * @param mixed $facet
     */
    public function setFacet($facet)
    {
        $this->facet = $facet;

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

    function toArray(){
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'facet' => $this->getFacet(),
            'description' => $this->getDescription(),
            'recursivity' => $this->getRecursivityArray()
        ];
    }

    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param Role $role
     * @return $this
     */
    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->addDateType($this);
        }

        return $this;
    }

    /**
     * @param Role $role
     * @return $this
     */
    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
            $role->removeDateType($this);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function resetRoles() :self
    {
        $this->roles = new ArrayCollection();
        return $this;
    }


    function __toString()
    {
        return $this->getLabel();
    }

    function trac(){
        return sprintf("%s (%s)", $this->getLabel(), $this->getDescription());
    }
}
