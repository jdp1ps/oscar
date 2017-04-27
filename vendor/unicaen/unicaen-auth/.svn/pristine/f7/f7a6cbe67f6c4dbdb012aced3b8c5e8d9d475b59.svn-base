<?php
namespace UnicaenAuth\Provider\Identity;

use BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider;
use BjyAuthorize\Provider\Role\ProviderInterface;
use UnicaenApp\Entity\Ldap\People;
use UnicaenAuth\Entity\Db\Role;
use UnicaenAuth\Service\Traits\RoleServiceAwareTrait;
use Zend\Ldap\Ldap;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use ZfcUser\Entity\UserInterface;
use Traversable;

/**
 * Classe de fournisseur d'identité issue de la base de données des utilisateurs.
 *
 * Retourne les rôles de l'utilisateur authentifié.
 * NB :
 * - Les ACL sont fournies par le service d'authorisation du module BjyAuthorize
 * - L'utilisateur authentifié est fournie par le service d'authentification.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class Db extends AuthenticationIdentityProvider implements ChainableProvider, \BjyAuthorize\Provider\Identity\ProviderInterface
{
    use RoleServiceAwareTrait;

    /**
     * @var Ldap
     */
    private $ldap;



    /**
     * {@inheritDoc}
     */
    public function injectIdentityRoles(ChainEvent $event)
    {
        $event->addRoles($this->getIdentityRoles());
    }



    /**
     * {@inheritDoc}
     */
    public function getIdentityRoles()
    {
        if (!($idArray = $this->authService->getIdentity())) {
            return [$this->defaultRole];
        }

        $ldapDn   = null;
        $identity = null;
        if (is_array($idArray)) {
            if (isset($idArray['ldap']) && $idArray['ldap'] instanceof People) {
                $ldapDn = $idArray['ldap']->getDn();
            }
            if (isset($idArray['db'])) {
                $identity = $idArray['db'];
            }
        }

        if ($identity instanceof ProviderInterface) {
            $roles = $identity->getRoles();
            if ($roles instanceof Traversable) {
                $roles = iterator_to_array($roles);
            }
        } else {
            $roles = [];
        }

        if ($identity instanceof UserInterface) {
            $roles[] = $identity->getUsername();
        }

        /* Injection des rôles par filtre LDAP */
        $ldapRoles = $this->getServiceRole()->getList();
        foreach ($ldapRoles as $role) {
            if ($role->getLdapFilter() && !in_array($role, $roles)) {
                if ($this->roleMatches($role, $ldapDn)) {
                    $roles[] = $role;
                }
            }
        }

        return $roles;
    }



    /**
     * @param Role   $role
     * @param string $dn
     *
     * @return bool
     */
    protected function roleMatches(Role $role, $dn)
    {
        try {
            return 1 === $this->getLdap()->count($role->getLdapFilter(), $dn, Ldap::SEARCH_SCOPE_SUB);
        } catch (\Zend\Ldap\Exception\LdapException $e) {
            return false;
        }
    }



    /**
     * Returns the LDAP Object
     *
     * @return Ldap
     */
    public function getLdap()
    {
        return $this->ldap;
    }



    /**
     * Set an Ldap connection
     *
     * @param  Ldap $ldap
     *
     * @return self
     */
    public function setLdap(Ldap $ldap)
    {
        $this->ldap = $ldap;

        return $this;
    }
}