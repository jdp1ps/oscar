<?php
namespace UnicaenAuth\Provider\Identity;

use BjyAuthorize\Provider\Identity\ProviderInterface;
use BjyAuthorize\Service\Authorize;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Permissions\Acl\Role\Registry;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Chaîne de responsabilité permettant à plusieures sources de fournir
 * les rôles (ACL) de l'identité authentifiée éventuelle.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see ChainEvent
 * @see \UnicaenAuth\Provider\Identity\ChainServiceFactory
 */
class Chain implements ProviderInterface, ServiceLocatorAwareInterface, EventManagerAwareInterface
{
    use ServiceLocatorAwareTrait;
    use EventManagerAwareTrait;

    /**
     * @var ChainEvent
     */
    protected $event;

    /**
     * @var array
     */
    protected $roles;

    /**
     * Retourne lee roles de l'utilisateur courant.
     * Si un rôle courant est sélectionné, c'est lui qu'on retourne.
     *
     * @return string[]|RoleInterface[]
     */
    public function getIdentityRoles()
    {
        $allRoles             = $this->getAllIdentityRoles();
        $selectedIdentityRole = $this->getSelectedIdentityRole();
        $roles                = $selectedIdentityRole ? [$selectedIdentityRole] : $allRoles;

        return $roles;
    }

    /**
     * Retourne l'éventuel rôle courant sélectionné.
     *
     * @return mixed
     */
    private function getSelectedIdentityRole()
    {
        return $this->getServiceLocator()->get('AuthUserContext')->getSelectedIdentityRole();
    }

    /**
     * Collecte tous les rôles de l'utilisateur.
     *
     * @return array
     */
    public function getAllIdentityRoles()
    {
        if (null !== $this->roles) {
            return $this->roles;
        }

        $this->roles = [];

        $e = $this->getEvent();
        $e->clearRoles();

        // collecte des rôles
        $this->getEventManager()->trigger('getIdentityRoles', $e);
        $roles = $e->getRoles();

        $authorizeService = $this->getServiceLocator()->get('BjyAuthorize\Service\Authorize'); /* @var $authorizeService Authorize */

        $registry = new Registry();
        foreach ($roles as $role) {
            // ne retient que les rôles déclarés dans les ACL
            if (!$authorizeService->getAcl()->hasRole($role)) {
                continue;
            }
            // évite les doublons
            if (!$registry->has($role)) {
                $role = $authorizeService->getAcl()->getRole($role);
                $registry->add($role);
                $this->roles[$role->getRoleId()] = $role;
            }
        }

//        var_dump($this->roles);

        return $this->roles;
    }

    /**
     *
     * @return ChainEvent
     */
    public function getEvent()
    {
        if (null === $this->event) {
            $this->event = new ChainEvent();
            $this->event->setTarget($this);
        }
        return $this->event;
    }

    /**
     *
     * @param ChainEvent $event
     * @return self
     */
    public function setEvent(ChainEvent $event)
    {
        $this->event = $event;
        return $this;
    }
}