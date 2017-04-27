<?php

namespace UnicaenAuth\Provider\Identity;

interface ChainableProvider
{
    /**
     * Injecte les rôles que possède l'identité authentifiée courante.
     *
     * @param ChainEvent $e
     */
    public function injectIdentityRoles(ChainEvent $e);
}