<?php

namespace UnicaenAuth\Event;

use UnicaenApp\Entity\Ldap\People;
use Zend\EventManager\Event;
use ZfcUser\Entity\UserInterface;

/**
 * Classe des événements déclenchés lors de l'authentification de l'utilisateur.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserAuthenticatedEvent extends Event
{
    const PRE_PERSIST      = 'prePersist';
    const PARAM_DB_USER    = 'db_user';
    const PARAM_LDAP_USER  = 'ldap_user';
    
    /**
     * Retourne l'entité utilisateur issue de la base de données.
     * 
     * @return UserInterface
     */
    public function getDbUser()
    {
        return $this->getParam(self::PARAM_DB_USER);
    }

    /**
     * Retourne l'entité utilisateur issue de l'annuaire LDAP.
     * 
     * @return People
     */
    public function getLdapUser()
    {
        return $this->getParam(self::PARAM_LDAP_USER);
    }

    /**
     * Spécifie l'entité utilisateur issue de la base de données.
     * 
     * @param UserInterface $dbUser
     * @return UserAuthenticatedEvent
     */
    public function setDbUser(UserInterface $dbUser)
    {
        $this->setParam(self::PARAM_DB_USER, $dbUser);
        return $this;
    }

    /**
     * Spécifie l'entité utilisateur issue de l'annuaire LDAP.
     * 
     * @param People $ldapUser
     * @return UserAuthenticatedEvent
     */
    public function setLdapUser(People $ldapUser)
    {
        $this->setParam(self::PARAM_LDAP_USER, $ldapUser);
        return $this;
    }
}