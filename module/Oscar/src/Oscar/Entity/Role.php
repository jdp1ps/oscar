<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 12/06/15 10:43
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use BjyAuthorize\Acl\HierarchicalRoleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use UnicaenAuth\Acl\NamedRole;

/**
 * Cette classe référence les rôles GLOBAUX sur l'application.
 *
 * @ORM\Entity
 * @ORM\Table(name="user_role")
 * @ORM\Entity(repositoryClass="RoleRepository")
 */
class Role implements HierarchicalRoleInterface
{
    const LEVEL_ACTIVITY = 1;
    const LEVEL_ORGANIZATION = 2;
    const LEVEL_APPLICATION = 4;


    /**
     * @ORM\OneToMany(targetEntity=TabsDocumentsRoles::class, mappedBy="role")
     */
    private $tabsDocumentsRoles;

    /**
     * Role constructor.
     * @param int $id
     */
    public function __construct()
    {
        $this->privileges = new ArrayCollection();
        $this->tabsDocumentsRoles = new ArrayCollection();
    }


    /**
     * @return Collection|TabsDocumentsRoles[]
     */
    public function getTabsDocumentsRoles(): Collection
    {
        return $this->tabsDocumentsRoles;
    }

    public function addTabDocumentRole(TabsDocumentsRoles $tabDocumentRole): self
    {
        if (!$this->tabsDocumentsRoles->contains($tabDocumentRole)) {
            $this->tabsDocumentsRoles[] = $tabDocumentRole;
            $tabDocumentRole->setRole($this);
        }

        return $this;
    }

    public function removeTabDocumentRole(TabsDocumentsRoles $tabDocumentRole): self
    {
        if ($this->tabsDocumentsRoles->contains($tabDocumentRole)) {
            $this->tabsDocumentsRoles->removeElement($tabDocumentRole);
            if ($tabDocumentRole->getRole() === $this) {
                $tabDocumentRole->setRole(null);
            }
        }

        return $this;
    }

    /**
     * Retourne l'instance rôle en conversion tableau clefs, valeurs
     * @return array
     */
    public function asArray()
    {
        return [
            'id' => $this->getId(),
            'roleId' => $this->getRoleId(),
            'spot' => $this->getSpot(),
            'ldapFilter' => $this->getLdapFilter(),
            'description' => $this->getDescription(),
            'principal' => $this->isPrincipal()
        ];
    }

    public function toJson()
    {
        return $this->asArray();
    }

    /**
     * @param $level
     * @return string
     */
    public static function getLevelLabel($level)
    {
        return self::getLevelLabels()[$level];
    }

    /**
     * Retourne les intitulés des niveaux d'application.
     * @return string[]
     */
    public static function getLevelLabels()
    {
        static $_levelLabels;
        if ($_levelLabels === null) {
            $_levelLabels = [
                self::LEVEL_APPLICATION => 'Application',
                self::LEVEL_ORGANIZATION => 'Organization',
                self::LEVEL_ACTIVITY => 'Activity',
            ];
        }

        return $_levelLabels;
    }

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="role_id", type="string", length=255, unique=true, nullable=false)
     */
    protected $roleId;


    /**
     * @var boolean
     * @ORM\Column(name="is_default", type="boolean", nullable=false)
     */
    protected $isDefault = false;

    /**
     * @var Role
     * @ORM\ManyToOne(targetEntity="Role")
     */
    protected $parent;

    /**
     * @var string
     * @ORM\Column(name="ldap_filter", type="string", length=255, unique=true, nullable=true)
     */
    protected $ldapFilter;

    /**
     * Fixe le niveau d'application (BIT).
     *
     * @var string
     * @ORM\Column(name="spot", type="integer", nullable=true, options={"default"=7})
     */
    protected $spot = 7;

    /**
     * Description du rôle.
     *
     * @var string
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    protected $description = "";

    /**
     * Est un rôle principal (Généralement responsable)
     *
     * @var boolean
     * @ORM\Column(name="principal", type="boolean", nullable=false, options={"default"=false})
     */
    protected $principal = false;


    /**
     * @ORM\ManyToMany(targetEntity="Privilege", mappedBy="role", fetch="EAGER")
     */
    protected $privileges;

    /**
     * @var boolean
     * @ORM\Column(name="accessible_exterieur", type="boolean", nullable=false, options={"default" : true})
     */
    protected $accessibleExterieur = true;


    ///////////////////////////////////////////////////////////////// PRIVILEGES

    /**
     * @return mixed
     */
    public function getPrivileges()
    {
        return $this->privileges;
    }

    /**
     * @return bool
     */
    public function isAccessibleExterieur(): bool
    {
        return $this->accessibleExterieur;
    }

    /**
     * @param bool $accessibleExterieur
     */
    public function setAccessibleExterieur(bool $accessibleExterieur): void
    {
        $this->accessibleExterieur = $accessibleExterieur;
    }


    /**
     * Test si le rôle dispose du privilège.
     *
     * @param $privilege
     * @throws \Exception
     */
    public function hasPrivilege($privilege)
    {
        /** @var Privilege $privilege */
        foreach ($this->getPrivileges() as $p) {
            if ($p->getFullCode() == $privilege) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retourne la liste des priviligèges du rôle sour la forme d'une liste de
     * chaîne de caractère.
     *
     * @throws \Exception
     */
    public function getPrivilegesArrayString()
    {
        throw new \Exception('NOT IMPLEMENTED');
    }

    ////////////////////////////////////////////////////////////////////////////

    /**
     * Get the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id.
     *
     * @param int $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = (int)$id;

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

    /**
     * @return string
     */
    public function getSpot()
    {
        return $this->spot;
    }

    /**
     * @param string $spot
     */
    public function setSpot($spot)
    {
        $this->spot = $spot;

        return $this;
    }

    /**
     * Get the role id.
     *
     * @return string
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set the role id.
     *
     * @param string $roleId
     *
     * @return self
     */
    public function setRoleId($roleId)
    {
        $this->roleId = (string)$roleId;

        return $this;
    }

    /**
     * Is this role the default one ?
     *
     * @return boolean
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set this role as the default one.
     *
     * @param boolean $isDefault
     *
     * @return self
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = (boolean)$isDefault;

        return $this;
    }

    /**
     * Get the parent role
     *
     * @return Role
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getLdapFilter()
    {
        return $this->ldapFilter;
    }

    /**
     * @param string $ldapFilter
     */
    public function setLdapFilter($ldapFilter)
    {
        $this->ldapFilter = $ldapFilter;

        return $this;
    }


    /**
     * Set the parent role.
     *
     * @param Role $role
     *
     * @return self
     */
    public function setParent(Role $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get users.
     *
     * @return array
     */
    public function getUsers()
    {
        return $this->users->getValues();
    }

    /**
     * Add a user to the role.
     *
     * @param User $user
     *
     * @return void
     */
    public function addUser($user)
    {
        $this->users[] = $user;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getRoleId();
    }

    private $_levels = null;

    public function getLevels()
    {
        if ($this->_levels === null) {
            $this->_levels = [];
            if ($this->isLevelApplication()) {
                $this->_levels[] = self::getLevelLabel(self::LEVEL_APPLICATION);
            }
            if ($this->isLevelOrganization()) {
                $this->_levels[] = self::getLevelLabel(self::LEVEL_ORGANIZATION);
            }
            if ($this->isLevelActivity()) {
                $this->_levels[] = self::getLevelLabel(self::LEVEL_ACTIVITY);
            }
        }

        return $this->_levels;
    }

    /**
     * Retourne TRUE si le role est du niveau demandé.
     * @param int $level
     * @return bool
     */
    public function isLevel(int $level): bool
    {
        return ($this->getSpot() & $level) > 0;
    }

    /**
     * Retourne TRUE si le rôle est au niveau applicatif
     * @return bool
     */
    public function isLevelApplication(): bool
    {
        return $this->isLevel(self::LEVEL_APPLICATION);
    }

    /**
     * Retourne TRUE si le rôle est au niveau organisation
     * @return bool
     */
    public function isLevelOrganization(): bool
    {
        return $this->isLevel(self::LEVEL_ORGANIZATION);
    }

    /**
     * Retourne TRUE si le rôle est au niveau activité
     * @return bool
     */
    public function isLevelActivity(): bool
    {
        return $this->isLevel(self::LEVEL_ACTIVITY);
    }
}
