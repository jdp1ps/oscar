<?php
namespace UnicaenApp\Mapper\Ldap;

use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Entity\Ldap\Group as LdapGroupModel;

/**
 * Classe regroupant les opérations de recherche de groupes dans l'annuaire LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Group extends AbstractMapper
{
    const GROUPS_BASE_DN = 'ou=groups,dc=unicaen,dc=fr';
    const CN_FILTER      = '(cn=%s)';
    
    /**
     * Retourne la liste des attributs LDAP remontés dans les résultats de recherches.
     * NB: l'attribut 'dn' est forcément inclus.
     * 
     * @return array e.g. array("mail", "sn", "cn")
     */
    public function getAttributes()
    {
        return array('*');
    }

    /**
     * Recherche un groupe par son DN.
     * 
     * @param string $dn DN
     * @return \UnicaenApp\Entity\Ldap\Group
     */
    public function findOneByDn($dn)
    {
        $filter = '(objectClass=*)';
        try {
            $entry = $this->searchSimplifiedEntry($filter, $dn);//, array(), null, \Zend\Ldap\Ldap::SEARCH_SCOPE_BASE);
        }
        catch (\Zend\Ldap\Exception\LdapException $le) {
            // entrée introuvable
            $entry = null;
        }
        if ($entry) {
            $entry = new LdapGroupModel($entry);
        }
        return $entry;
    }
    
    /**
     * Recherche de groupes par leur DN.
     * 
     * @param array|string $dn DN
     * @return array(dn=>Group)
     */
    public function findAllByDn($dn)
    {
        $entries = array();
        foreach ($dns = (array)$dn as $dn) {
            if (($entry = $this->findOneByDn($dn))) {
                $entries[$entry->getDn()] = $entry;
            }
        }
        return $entries;
    }

    /**
     * Recherche un groupe par son CN.
     * 
     * @param string $cn CN
     * @return \UnicaenApp\Entity\Ldap\Group
     */
    public function findOneByCn($cn)
    {
        $filter = sprintf(self::CN_FILTER, $cn);
        $entry = $this->searchSimplifiedEntry($filter, self::GROUPS_BASE_DN);
        if ($entry) {
            $entry = new LdapGroupModel($entry);
        }
        return $entry;
    }
    
    /**
     * Recherche de groupes par leur CN.
     * 
     * @param array|string $cn CN
     * @return array(dn=>Group)
     */
    public function findAllByCn($cn)
    {
        $cn = (array)$cn;
        $filter = '(|' . implode('', array_fill(0, count($cn), self::CN_FILTER)) . ')';
        $filter = vsprintf($filter, $cn);
        $entries = $this->searchSimplifiedEntries($filter, self::GROUPS_BASE_DN);
        if ($entries) {
            $entries = LdapGroupModel::getInstances($entries);
        }
        return $entries;
    }
    
    /**
     * Recherche tous les groupes.
     * 
     * @return array(dn=>Group)
     */
    public function findAll()
    {
        $filter = '(objectClass=*)';
        $entries = $this->searchSimplifiedEntries($filter, self::GROUPS_BASE_DN);
        if ($entries) {
            $entries = LdapGroupModel::getInstances($entries);
        }
        return $entries;
    }
    
    /**
     * Filtre des groupes pour ne retenir que ceux dont la date de fin est postérieure à une date d'observation.
     * 
     * @param array(Group)|Group $groups Groupes à filtrer
     * @param \DateTime $dateObservation Date d'observation (date du jour si absente),
     * si la date de fin du groupe est antérieure à la date d'observation, le groupe n'est pas retenu
     * @param \UnicaenApp\Mapper\Ldap\Group $mapper Mapper de recherche des groupes dans l'annnuaire LDAP, requis si
     * les groupes sont spécifiés par leur CN
     * @return array(dn=>Group) Groupes retenus
     */
    public static function filterGroupsByDateFin($groups, \DateTime $dateObservation = null, \UnicaenApp\Mapper\Ldap\Group $mapper = null)
    {
        if (!is_array($groups)) {
            $groups = array($groups);
        }
        if (null === $dateObservation) {
            $dateObservation = new \DateTime();
        }
        $filteredGroups = array();
        foreach ($groups as $group) {
            if (!is_object($group)) {
                if (!$mapper) {
                    throw new LogicException("Le mapper est requis car un groupe n'est pas fourni au format objet.");
                }
                $cn = count($tmp = ldap_explode_dn($group, 1)) > 1 ? $tmp[0] : $group;
                $group = $mapper->findOneByCn($cn);
                if (!$group) {
                    throw new RuntimeException("Groupe LDAP introuvable avec le CN '$cn'.");
                }
            }
            if ($dateObservation > $group->getSupannGroupeDateFin()) {
                continue;
            }
            $filteredGroups[$group->getDn()] = $group;
        }
        return $filteredGroups;
    }
}