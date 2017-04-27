<?php
namespace UnicaenAuth\Entity\Ldap;

use UnicaenApp\Entity\Ldap\People as BasePeople;
use BjyAuthorize\Provider\Role\ProviderInterface;
use ZfcUser\Entity\UserInterface;

/**
 * Classe d'encapsulation d'un individu LDAP pour compatibilité avec l'interface
 * utilsée par le module ZfcUser.
 *
 * @see \UnicaenApp\Entity\Ldap\People
 * @see \ZfcUser\Entity\UserInterface
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class People extends BasePeople implements UserInterface, ProviderInterface
{
    /**
     * Constructeur.
     * 
     * @param array|BasePeople $people 
     */
    public function __construct($data = null)
    {
        if ($data instanceof BasePeople) {
            $data = $data->getData();
        }
        parent::__construct($data);
    }

    /**
     * {@inheritDoc}
     */
    public function getDisplayName()
    {
        return $this->getNomComplet(true);
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail()
    {
        return $this->mail;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->uid;
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->supannaliaslogin;
    }

    /**
     * {@inheritDoc}
     */
    public function setDisplayName($displayName)
    {
        throw new \BadMethodCallException("Forbidden!");
    }

    /**
     * {@inheritDoc}
     */
    public function setEmail($email)
    {
        throw new \BadMethodCallException("Forbidden!");
    }

    /**
     * 
     * @param int $id
     * @return self
     */
    public function setId($id)
    {
        throw new \BadMethodCallException("Forbidden!");
    }

    /**
     * {@inheritDoc}
     */
    public function setPassword($password)
    {
        throw new \BadMethodCallException("Forbidden!");
    }

    /**
     * {@inheritDoc}
     */
    public function setUsername($username)
    {
        throw new \BadMethodCallException("Forbidden!");
    }
    
    /**
     * Get state.
     *
     * @return int
     */
    public function getState()
    {
        return in_array('deactivated', ldap_explode_dn($this->getDn(), 1)) ? 0 : 1;
    }

    /**
     * Set state.
     *
     * @param int $state
     * @return UserInterface
     */
    public function setState($state)
    {
        throw new \BadMethodCallException("Forbidden!");
    }
    
    /**
     * @return \Zend\Permissions\Acl\Role\RoleInterface[]
     */
    public function getRoles()
    {
        $roles = array_merge($this->getMemberOf(), $this->getSupannRolesEntite());
        
        return $roles;
    }
}