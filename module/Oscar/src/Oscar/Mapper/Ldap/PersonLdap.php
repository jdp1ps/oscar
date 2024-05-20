<?php
/**
 * @author Joachim Dornbusch<joachim.dornbusch@univ-paris1.fr>
 * @date: 2024-05-17
 */

namespace Oscar\Mapper\Ldap;

use UnicaenApp\Mapper\Ldap\AbstractMapper;

/**
 * Classe regroupant les opérations de recherche de personnes dans l'annuaire LDAP pour l'import
 *
 * @author Unicaen
 */
class PersonLdap extends AbstractMapper
{
    /**
     * Retourne la liste des attributs LDAP demandés au LDAP pour les organisations
     * NB: l'attribut 'dn' est forcément inclus.
     *
     * @return array e.g. array("mail", "sn", "cn")
     */
    public function getAttributes()
    {
        return [
            'buildingname',//OK
            'dn',
            'edupersonaffiliation',
            'edupersonorgunitdn',
            'givenname',
            'labeleduri',
            'mail',
            'modifytimestamp',
            'postaladdress',
            'sn',
            'supannaliaslogin',
            'supannentiteaffectation',
            'supannentiteaffectationprincipale',
            'supannroleentite',
            'uid',
            'telephonenumber',
        ];
    }

    /**
     * Recherche une liste de structures par catégorie
     *
     * @param string $categoryFilter
     * @return array
     */
    public function findByCategoryFilter($categoryFilter): array
    {
        $filter = sprintf($this->configParam('filters', 'FILTER_PERSON_AFFILIATION'), $categoryFilter);
        return $this->searchSimplifiedEntries($filter, $this->configParam('dn', 'UTILISATEURS_BASE_DN'));
    }

    /**
     * Recherche une ou des personnes par uid
     *
     * @param string $uid
     * @return array
     */
    public function findByCode($uid): array
    {
        $filter = sprintf($this->configParam('filters', 'UID_FILTER'), $uid);
        return $this->searchSimplifiedEntries($filter, $this->configParam('dn', 'UTILISATEURS_BASE_DN'));
    }
}
