<?php
namespace UnicaenApp\Service\Ldap;

/**
 * Service d'accès aux structures de l'annuaire LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class Structure extends AbstractService
{
    
    /**
     * Retourne le mapper LDAP à utiliser.
     * 
     * @return \UnicaenApp\Mapper\Ldap\Structure
     */
    public function getMapper()
    {
        if (null === $this->mapper) {
            $this->mapper = new \UnicaenApp\Mapper\Ldap\Structure();
        }
        return $this->mapper;
    }

}
