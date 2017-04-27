<?php

namespace UnicaenAuth\Service;

use BjyAuthorize\Acl\Role;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Traits\SessionContainerTrait;
use UnicaenAuth\Provider\Identity\Chain;
use Zend\Session\Container as SessionContainer;
use Zend\Permissions\Acl\Role\RoleInterface;
use ZfcUser\Entity\UserInterface;
use UnicaenAuth\Entity\Ldap\People;
use UnicaenAuth\Acl\NamedRole;

/**
 * Service centralisant des méthodes utiles concernant l'utilisateur authentifié.
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class UserContext extends AbstractService
{
    use SessionContainerTrait;

    /**
     * @var mixed
     */
    protected $identity;

    /**
     * @var array
     */
    protected $identityRoles;



    /**
     * Retourne l'utilisateur BDD courant
     *
     * @return UserInterface
     */
    public function getDbUser()
    {
        if (($identity = $this->getIdentity())) {
            if (isset($identity['db']) && $identity['db'] instanceof UserInterface) {
                return $identity['db'];
            }
        }

        return null;
    }



    /**
     * Retourne l'utilisateur LDAP courant
     *
     * @return People
     */
    public function getLdapUser()
    {
        if (($identity = $this->getIdentity())) {
            if (isset($identity['ldap']) && $identity['ldap'] instanceof People) {
                return $identity['ldap'];
            }
        }

        return null;
    }



    /**
     * Retourne les données d'identité correspondant à l'utilisateur courant.
     *
     * @return mixed
     */
    public function getIdentity()
    {
        if (null === $this->identity) {
            $authenticationService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
            if ($authenticationService->hasIdentity()) {
                $this->identity = $authenticationService->getIdentity();
            }
        }

        return $this->identity;
    }



    /**
     * @param string $roleId
     *
     * @return Role
     */
    public function getIdentityRole($roleId)
    {
        $roles = $this->getServiceAuthorize()->getRoles();
        if (isset($roles[$roleId])) {
            return $roles[$roleId];
        }

        return null;
    }



    /**
     * Retourne tous les rôles de l'utilisateur courant, pas seulement le rôle courant sélectionné.
     *
     * Les clés du tableau sont les ID de rôles, les valeurs sont les objets Role
     *
     * @return Role[]
     */
    public function getIdentityRoles()
    {
        if (null === $this->identityRoles) {
            $this->identityRoles = [];

            $roles            = $this->getServiceAuthorize()->getRoles();
            $identityProvider = $this->getIdentityProvider();
            if ($identityProvider instanceof Chain) {
                $iRoles = $identityProvider->getAllIdentityRoles();
            } else {
                $iRoles = $identityProvider->getIdentityRoles();
            }
            foreach ($iRoles as $role) {
                if ($role instanceof Role) {
                    $this->identityRoles[$role->getRoleId()] = $role;
                } elseif (isset($roles[$role])) {
                    $role                                    = $roles[$role];
                    $this->identityRoles[$role->getRoleId()] = $role;
                }
            }
        }

        return $this->identityRoles;
    }



    /**
     * Retourne parmi tous les rôles de l'utilisateur courant ceux qui peuvent être sélectionnés.
     *
     * @return array
     */
    public function getSelectableIdentityRoles()
    {
        $filter = function ($r) {
            return !($r instanceof NamedRole && !$r->getSelectable());
        };
        $roles  = array_filter($this->getIdentityRoles(), $filter);

        return $roles;
    }



    /**
     * Si un utilisateur est authentifié, retourne le rôle utilisateur sélectionné,
     * ou alors le premier sélectionnable si aucun n'a été sélectionné.
     *
     * NB: Si un rôle est spécifié en session comme devant être le prochain rôle sélectionné,
     * c'est lui qui est pris en compte.
     *
     * @return mixed
     */
    public function getSelectedIdentityRole()
    {

        if ($this->getNextSelectedIdentityRole()) {
            $this->getSessionContainer()->selectedIdentityRole = $this->getNextSelectedIdentityRole();
        }

        if (null === $this->getSessionContainer()->selectedIdentityRole && $this->getIdentity()) {
            $roles = $this->getSelectableIdentityRoles();
            $this->setSelectedIdentityRole(reset($roles));
        }

        $roleId = $this->getSessionContainer()->selectedIdentityRole;

        if ($roleId) {

            $roles = $this->getServiceAuthorize()->getRoles(); // Récupération de tous les rôles du provider
            if (isset($roles[$roleId])) {
                $role = $roles[$roleId];
            } else {
                $role = null;
            }

            if ($this->isRoleValid($role)) {
                return $role;
            }
        }

        return null;
    }



    /**
     * Mémorise en session le rôle spécifié comme étant le rôle courant de l'utilisateur.
     *
     * NB: seul l'id du rôle est mémorisé en session.
     *
     * @param RoleInterface|string $role
     *
     * @return \UnicaenAuth\Service\UserContext
     * @throws RuntimeException
     */
    public function setSelectedIdentityRole($role)
    {
        if ($role) {
            if (!$this->isRoleValid($role)) {
                throw new RuntimeException("Rôle spécifié invalide.");
            }
            if ($role instanceof RoleInterface) {
                $role = $role->getRoleId();
            }
            $this->getSessionContainer()->selectedIdentityRole = $role;
        } else {
            unset($this->getSessionContainer()->selectedIdentityRole);
        }

        return $this;
    }



    /**
     * Retourne l'éventuel rôle spécifié en session devant être le prochain rôle sélectionné.
     *
     * @return string|null
     */
    public function getNextSelectedIdentityRole()
    {
        return $this->getSessionContainer()->nextSelectedIdentityRole;
    }



    /**
     * Mémorise en session le rôle devant être le prochain rôle sélectionné.
     *
     * NB: seul l'id du rôle est mémorisé en session ; la durée de vie du stockage est de 1 requête seulement.
     *
     * @param RoleInterface|string $role
     *
     * @return \UnicaenAuth\Service\UserContext
     */
    public function setNextSelectedIdentityRole($role)
    {
        if ($role instanceof RoleInterface) {
            $role = $role->getRoleId();
        }

        if ($role) {
            $this->getSessionContainer()->nextSelectedIdentityRole = $role;
            $this->getSessionContainer()->setExpirationHops(1, 'nextSelectedIdentityRole');
        } else {
            unset($this->getSessionContainer()->nextSelectedIdentityRole);
        }

        return $this;
    }



    /**
     * Teste si le rôle spécifié fait partie des rôles disponibles.
     *
     * @param RoleInterface|string $role
     *
     * @return boolean
     */
    protected function isRoleValid($role)
    {
        if ($role instanceof RoleInterface) {
            $role = $role->getRoleId();
        }

        foreach ($this->getIdentityRoles() as $r) {
            if ($r instanceof RoleInterface) {
                $r = $r->getRoleId();
            }
            if ($role === $r) {
                return true;
            }
        }

        return false;
    }



    /**
     *
     * @return \UnicaenAuth\Provider\Identity\Chain
     */
    private function getIdentityProvider()
    {
        return $this->getServiceAuthorize()->getIdentityProvider();
        /* @var $identityProvider \UnicaenAuth\Provider\Identity\Chain */
    }

}