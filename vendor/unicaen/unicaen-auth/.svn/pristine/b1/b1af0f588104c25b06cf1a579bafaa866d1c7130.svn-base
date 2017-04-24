<?php

namespace UnicaenAuth\Entity\Db;

use UnicaenAuth\Provider\Privilege\Privileges;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="privilege")
 */
class Privilege implements ResourceInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="code", type="string", length=150, unique=false, nullable=false)
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(name="libelle", type="string", length=200, unique=false, nullable=false)
     */
    private $libelle;

    /**
     * @var int
     * @ORM\Column(name="ordre", type="integer", unique=false, nullable=true)
     */
    private $ordre;

    /**
     * @var CategoriePrivilege
     * @ORM\ManyToOne(targetEntity="CategoriePrivilege", inversedBy="privilege")
     * @ORM\JoinColumn(name="categorie_id", referencedColumnName="id")
     */
    private $categorie;

    /**
     * @ORM\ManyToMany(targetEntity="UnicaenAuth\Entity\Db\Role",cascade={"all"})
     * @ORM\JoinTable(
     *     name="role_privilege",
     *     joinColumns={@ORM\JoinColumn(name="privilege_id", referencedColumnName="id", onDelete="cascade")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="cascade")}
     *
     * )
     */
    private $role;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->role = new \Doctrine\Common\Collections\ArrayCollection();
    }



    /**
     * Set code
     *
     * @param string $code
     *
     * @return Privilege
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }



    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }



    public function getFullCode()
    {
        return $this->getCategorie()->getCode() . '-' . $this->getCode();
    }



    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Privilege
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }



    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }



    /**
     *
     * @return integer
     */
    function getOrdre()
    {
        return $this->ordre;
    }



    /**
     *
     * @param integer $ordre
     *
     * @return self
     */
    function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * Set categorie
     *
     * @param CategoriePrivilege $categorie
     *
     * @return self
     */
    public function setCategorie(CategoriePrivilege $categorie = null)
    {
        $this->categorie = $categorie;

        return $this;
    }



    /**
     * Get categorie
     *
     * @return CategoriePrivilege
     */
    public function getCategorie()
    {
        return $this->categorie;
    }



    /**
     * Add role
     *
     * @param Role $role
     *
     * @return self
     */
    public function addRole(Role $role)
    {
        $this->role->add($role);

        return $this;
    }



    /**
     * Remove role
     *
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        $this->role->removeElement($role);
    }



    /**
     * Get role
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRole()
    {
        return $this->role;
    }



    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLibelle();
    }



    /**
     * @return string
     */
    public function getResourceId()
    {
        return Privileges::getResourceId($this);
    }
}
