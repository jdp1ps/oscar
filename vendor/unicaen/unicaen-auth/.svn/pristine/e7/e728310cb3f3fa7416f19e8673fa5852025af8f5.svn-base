<?php
namespace UnicaenAuth\Provider\Identity;

use BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider;

/**
 * Classe de fournisseur d'identité "authentifié" ou "non authentifié".
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class Basic extends AuthenticationIdentityProvider implements ChainableProvider
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
        if (! $identity = $this->authService->getIdentity()) {
            return [$this->getDefaultRole()];
        }

        return [$this->getAuthenticatedRole()];
    }
}