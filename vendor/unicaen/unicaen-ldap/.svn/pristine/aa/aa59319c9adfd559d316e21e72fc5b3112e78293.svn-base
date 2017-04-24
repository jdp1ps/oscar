<?php
namespace UnicaenLdap\Service;

/**
 * Classe regroupant les opérations de recherche de structures dans l'annuaire LDAP.
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 */
class Structure extends Service
{

    protected $type = 'Structure';

    protected $ou = array('structures');

    /**
     * Retourne la structure mère : Université
     *
     * @return UnicaenLdap\Entity\Structure
     */
    public function getUniv()
    {
        return $this->get('HS_UNIV');
    }
}