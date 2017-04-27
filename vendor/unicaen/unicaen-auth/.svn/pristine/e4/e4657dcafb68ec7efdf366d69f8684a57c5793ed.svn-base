<?php

namespace UnicaenAuth\Service;

/**
 * Interface spécifiant une dépendance avec un utilisateur issu de l'annuaire LDAP.
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
interface LdapUserAwareInterface
{
    /**
     * Injecte l'utilisateur.
     *
     * @param People $user
     */
    public function setLdapUser(\UnicaenAuth\Entity\Ldap\People $user);

    /**
     * Retourne l'utilisateur injecté.
     *
     * @return \UnicaenAuth\Entity\Ldap\People
     */
    public function getLdapUser();
}