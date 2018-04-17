<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/11/15 10:09
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class OrganizationType
 * @package Oscar\Entity
 * @ORM\Entity
 */
class OrganizationType implements ITrackable
{
    use TraitTrackable;

    /**
     * @var OrganizationType
     * @ORM\ManyToOne(targetEntity="OrganizationType", inversedBy="children")
     */
    private $root;

    /**
     * Liste des affectations.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="OrganizationType", mappedBy="root")
     */
    protected $children;

    /**
     * Intitulé du type
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $label;

    /**
     * Description.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $description;

    /**
     * OrganizationType constructor.
     * @param ArrayCollection $children
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param mixed $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param ArrayCollection $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
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

    function toJson(){
        $children = [];
        foreach ($this->getChildren() as $c ){
            $children[] = $c->toJson();
        }
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'root_id' => $this->getRoot() ? $this->getRoot()->getId() : null,
            'children' => $children
        ];
    }

}
