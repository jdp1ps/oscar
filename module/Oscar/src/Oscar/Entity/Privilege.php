<?php

namespace Oscar\Entity;

use Oscar\Entity\Role;
use Oscar\Provider\Privileges;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="privilege")
 * @ORM\Entity(repositoryClass="Oscar\Entity\PrivilegeRepository")
 */
class Privilege implements ResourceInterface
{
    const LEVEL_ACTIVITY = 1;
    const LEVEL_ORGANIZATION = 2;
    const LEVEL_APPLICATION = 4;

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
     * @ORM\ManyToOne(targetEntity="CategoriePrivilege", inversedBy="privilege", fetch="EAGER")
     * @ORM\JoinColumn(name="categorie_id", referencedColumnName="id")
     */
    private $categorie;

    /**
    * @var int
    * @ORM\Column(name="spot", type="integer", nullable=true, options={"default"=7})
    */
    private $spot = 7;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="privileges")
     * @ORM\JoinTable(
     *     name="role_privilege",
     *     joinColumns={@ORM\JoinColumn(name="privilege_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *
     * )
     */
    private $role;


    /**
     * @var Privilege
     * @ORM\ManyToOne(targetEntity="Privilege", inversedBy="children")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $root;

    /**
     * @ORM\OneToMany(targetEntity="Privilege", mappedBy="root")
     */
    private $children;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->role = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return int
     */
    public function getSpot(): int
    {
        return $this->spot;
    }

    /**
     * @param int $spot
     */
    public function setSpot($spot)
    {
        $this->spot = $spot;

        return $this;
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
     * @return Privilege|null
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param Privilege $root
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children)
    {
        $this->children = $children;

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
     * @param \Oscar\Entity\Role $role
     * @return bool
     */
    public function hasRole(Role $role)
    {
        return $this->role->contains($role);
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
        return 'Privilege';
    }

    public function getRoleIds()
    {
        $roleIds = [];
        foreach( $this->getRole() as $role ){
            $roleIds[] = $role->getId();
        }
        return $roleIds;
    }


    public function asArray($flat = false)
    {
        $data = [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'libelle' => $this->getLibelle(),
            'categorie' => $this->getCategorie()->toArray(),
            'roles' => $this->getRoleIds(),
            'root' => $this->getRoot(),
            'spot' => $this->getSpot()
        ];

        if( $flat != true ){
            $data['children'] = [];
            foreach ($this->getChildren() as $child ){
                $data['children'][] = $child->asArray();
            }
        }

        return $data;
    }
}
