<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 12/06/15 10:43
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use BjyAuthorize\Acl\HierarchicalRoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Cette classe référence les rôles GLOBAUX sur l'application.
 *
 */
class RoleOrganization
{

    public function asArray()
    {
        return [
            'id' => $this->getId(),
            'roleId' => $this->getRoleId(),
            'description' => $this->getDescription(),
            'principal' => (boolean)$this->isPrincipal()
        ];
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
     * Description du rôle.
     *
     * @var string
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    protected $description = "";

    /**
     * Est un rôle principal
     *
     * @var boolean
     * @ORM\Column(name="principal", type="boolean", nullable=false, options={"default"=false})
     */
    protected $principal = false;


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
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getRoleId();
    }
}