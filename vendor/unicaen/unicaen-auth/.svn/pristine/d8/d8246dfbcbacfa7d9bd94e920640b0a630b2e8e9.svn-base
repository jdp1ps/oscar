<?php
namespace UnicaenAuth\Acl;

use Zend\Permissions\Acl\Role\RoleInterface;

/**
 * Rôle avec nom explicite (humainement intelligible).
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NamedRole extends \BjyAuthorize\Acl\Role
{
    /**
     * @var string
     */
    protected $roleName;
    
    /**
     * @var string
     */
    protected $roleDescription;

    /**
     * @var bool
     */
    protected $selectable = true;
    

    /**
     * Constructeur.
     * 
     * @param string|null               $id
     * @param RoleInterface|string|null $parent
     * @param string                    $name
     * @param string                    $description
     * @param bool                      $selectable
     */
    public function __construct($id = null, $parent = null, $name = null, $description = null, $selectable = true)
    {
        parent::__construct($id, $parent);
        
        $this
                ->setRoleName($name ?: $id)
                ->setRoleDescription($description ?: null)
                ->setSelectable($selectable);
    }

    /**
     * Retourne la représentation littérale de ce rôle.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getRoleName() ?: $this->getRoleId();
    }
    
    /**
     * Retourne le nom du rôle.
     * 
     * @return string
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * Spécifie le nom du rôle.
     * 
     * @param string $roleName
     * @return self
     */
    public function setRoleName($roleName)
    {
        $this->roleName = (string) $roleName;
        return $this;
    }

    /**
     * Retourne la description du rôle.
     * 
     * @return string
     */
    public function getRoleDescription()
    {
        return $this->roleDescription;
    }

    /**
     * Spécifie la description du rôle.
     * 
     * @param string $roleDescription
     * @return self
     */
    public function setRoleDescription($roleDescription)
    {
        $this->roleDescription = (string) $roleDescription;
        return $this;
    }
    
    /**
     * Teste si ce rôle est sélectionnable par l'utilisateur. 
     * Cela concerne les applications qui permettent à l'utilisateur de choisir son profil courant
     * parmi les différents rôles qu'il possède.
     * 
     * @return bool
     */
    public function getSelectable()
    {
        return $this->selectable;
    }

    /**
     * Spécifie si ce rôle est sélectionnable par l'utilisateur. 
     * Cela concerne les applications qui permettent à l'utilisateur de choisir son profil courant
     * parmi les différents rôles qu'il possède.
     * 
     * @param bool $selectable
     * @return \UnicaenAuth\Acl\NamedRole
     */
    public function setSelectable($selectable = true)
    {
        $this->selectable = $selectable;
        return $this;
    }
}