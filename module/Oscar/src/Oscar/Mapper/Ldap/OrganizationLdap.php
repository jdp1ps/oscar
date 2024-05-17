<?php
/**
 * @author Joachim Dornbusch<joachim.dornbusch@univ-paris1.fr>
 * @date: 2024-05-17
 */
namespace Oscar\Mapper\Ldap;

use UnicaenApp\Mapper\Ldap\AbstractMapper;

/**
 * Classe regroupant les opérations de recherche de structure dans l'annuaire LDAP.
 *
 * @author Unicaen
 */
class OrganizationLdap extends AbstractMapper
{
    /**
     * Retourne la liste des attributs LDAP remontés dans les résultats de recherches.
     * NB: l'attribut 'dn' est forcément inclus.
     *
     * @return array e.g. array("mail", "sn", "cn")
     */
    public function getAttributes()
    {
        return ['*'];
    }

    /**
     * Recherche une liste de structures par catégorie
     *
     * @param string $categoryFilter
     * @return array
     */
    public function findByCategoryFilter($categoryFilter): array
    {
        $filter = sprintf($this->configParam('filters', 'FILTER_STRUCTURE_DN'), $categoryFilter);
        return $this->searchSimplifiedEntries($filter, $this->configParam('dn', 'STRUCTURES_BASE_DN'));
    }

    /**
     * Recherche une ou des structures par supannCodeEntite
     *
     * @param string $supannCodeEntite
     * @return array
     */
    public function findByCode($supannCodeEntite): array
    {
        $filter = sprintf($this->configParam('filters', 'FILTER_STRUCTURE_CODE_ENTITE'), $supannCodeEntite);
        return $this->searchSimplifiedEntries($filter, $this->configParam('dn', 'STRUCTURES_BASE_DN'));
    }
}
