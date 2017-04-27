<?php
namespace UnicaenApp\Service\Ldap;

/**
 * Service d'accès aux individus de l'annuaire LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class People extends AbstractService
{
    
    /**
     * Retourne le mapper LDAP à utiliser.
     * 
     * @return \UnicaenApp\Mapper\Ldap\People
     */
    public function getMapper()
    {
        if (null === $this->mapper) {
            $this->mapper = new \UnicaenApp\Mapper\Ldap\People();
        }
        return $this->mapper;
    }

}
