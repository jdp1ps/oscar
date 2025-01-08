<?php
/**
 * @author Joachim Dornbusch<joachim.dornbusch@univ-paris1.fr>
 * @date: 2024-05-17
 */

namespace Oscar\Mapper\Ldap;

use UnicaenApp\Mapper\Ldap\AbstractMapper;

/**
 * Classe regroupant les opérations de recherche de structures dans l'annuaire LDAP pour l'import
 *
 */
class OrganizationLdap extends AbstractMapper
{
    /**
     * Retourne la liste des attributs LDAP demandés au LDAP pour les personnes
     * NB: l'attribut 'dn' est forcément inclus.
     *
     * @return array e.g. array("mail", "sn", "cn")
     */
    public function getAttributes()
    {
        return [
            'businesscategory',
            'description',
            'labeleduri',
            'modifytimestamp',
            'ou',
            'info',
            'mail',
            'postaladdress',
            'supanncodeentite',
            'supannrefid',
            'supanntypeentite',
            'telephonenumber',
            'eduOrgHomePageURI',
            'eduOrgLegalName'
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
        $filter = sprintf($this->configParam('filters', 'FILTER_STRUCTURE_DN'), $categoryFilter);
        //FIXME Trying to access array offset on value of type bool
        // https://github.com/laminas/laminas-ldap/issues/6
        return $this->searchSimplifiedEntries($filter, $this->configParam('dn', 'STRUCTURES_BASE_DN'));
    }

    /**
     * Uniquement pour les tests check:config
     *
     * @return array
     */
    public function searchFirstEntry() : array
    {
        $entries = $this->getLdap()->searchEntries('(supannCodeEntite=*)',
            $this->configParam('dn', 'STRUCTURES_BASE_DN'),
            1, [], null, false, 1);
        foreach ($entries as $i => $entry) {
            $entries[$i] = self::simplifiedEntry($entry);
        }
        return $entries;
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
