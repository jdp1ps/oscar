<?php
namespace UnicaenLdap\Service;

use DateTime;

/**
 * Classe regroupant les opérations de recherche de groupes dans l'annuaire LDAP.
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 */
class Group extends Service
{

    protected $type = 'Group';

    protected $ou = array('groups');


    


    /**
     * Filtre une liste de groupes pour ne retourner que ceux qui sont encore valides
     *
     * @param array|Collection $groups
     * @return type
     */
    public function filterValids( $groups, DateTime $dateObservation = null )
    {
        if (empty($dateObservation)) $dateObservation = new DateTime;

        $result = array();
        foreach( $groups as $id => $group ){
            if ($group->isValid($dateObservation)) $result[$id] = $group;
        }
        return $result;
    }
}