<?php
namespace UnicaenAuth\Provider\Identity;

use Zend\EventManager\Event;
use Traversable;

/**
 * Événement propagé dans la chaîne de responsabilité de collecte des rôles
 * de l'identité connectée.
 *
 * @see Chain
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ChainEvent extends Event
{
    /**
     * Rôles collectés.
     *
     * @var array
     */
    protected $roles = [];

    /**
     * Retourne les rôles collectés.
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Ajoute des rôles aux rôles collectés.
     *
     * @param array|Traversable $roles
     * @return self
     */
    public function addRoles($roles)
    {
        if ($roles instanceof Traversable) {
            $roles = iterator_to_array($roles);
        }
        $this->roles = array_merge($this->roles, $roles);
        return $this;
    }

    /**
     * Vide les rôles collectés.
     *
     * @return self
     */
    public function clearRoles()
    {
        $this->roles = [];
        return $this;
    }
}