<?php
namespace UnicaenAuth\Provider\Identity;

use BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider;
use BjyAuthorize\Provider\Role\ProviderInterface;
use ZfcUser\Entity\UserInterface;

/**
 * Classe de fournisseur d'identité issue de l'annuaire Ldap.
 *
 * Retourne les rôles correspondant aux groupes LDAP auxquels appartient l'entité LDAP authentifiée.
 * NB :
 * - Les ACL sont fournies par le service d'authorisation du module BjyAuthorize
 * - L'identité authentifiée est fournie par le service d'authentification.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @deprecated
 */
class Ldap extends AuthenticationIdentityProvider implements ChainableProvider
{
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
        if (!($identity = $this->authService->getIdentity())) {
            return [$this->defaultRole];
        }

        if (is_array($identity) && isset($identity['ldap'])) {
            $identity = $identity['ldap'];
        }

        if ($identity instanceof ProviderInterface) {
            $roles = $identity->getRoles();
            if ($roles instanceof Traversable) {
                $roles = iterator_to_array($roles);
            }
        }
        else {
            $roles = [];
        }

        if ($identity instanceof UserInterface) {
            $roles[] = $identity->getUsername();
        }

        return $roles;
    }
}