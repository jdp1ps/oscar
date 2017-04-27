<?php
namespace UnicaenApp\Service\Ldap;

/**
 * Service d'accès aux groupes de l'annuaire LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class Group extends AbstractService
{
    
    /**
     * Retourne le mapper LDAP à utiliser.
     * 
     * @return \UnicaenApp\Mapper\Ldap\Group
     */
    public function getMapper()
    {
        if (null === $this->mapper) {
            $this->mapper = new \UnicaenApp\Mapper\Ldap\Group();
        }
        return $this->mapper;
    }

}
