<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link    https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace UnicaenAuth\Entity\Db;

use BjyAuthorize\Acl\HierarchicalRoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * An example entity that represents a role.
 *
 * @ORM\Entity
 * @ORM\Table(name="user_role")
 */
class Role implements HierarchicalRoleInterface
{
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
     * @ORM\Column(name="is_default", type="boolean", nullable=true)
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
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="UnicaenAuth\Entity\Db\User")
     * @ORM\JoinTable(name="user_role_linker",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    protected $users;



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
     * @return string
     */
    public function getLdapFilter()
    {
        return $this->ldapFilter;
    }



    /**
     * @param string $ldapFilter
     *
     * @return Role
     */
    public function setLdapFilter($ldapFilter)
    {
        $this->ldapFilter = $ldapFilter;

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
}