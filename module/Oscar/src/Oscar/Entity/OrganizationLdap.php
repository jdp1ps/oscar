<?php

namespace Oscar\Entity;

use UnicaenApp\Entity\Ldap\Structure as LdapStructureModel;
use UnicaenApp\Mapper\Ldap\AbstractMapper;

/**
 * Classe regroupant les opérations de recherche de structure dans l'annuaire LDAP.
 *
 * @author Unicaen
 */
class OrganizationLdap extends AbstractMapper
{
    const CHEMIN_INTROUVABLE = "(INTROUVABLE DANS ANNUAIRE)";

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
     * Recherche une liste de structures
     *
     * @param string $filterName
     * @param bool   $tryDeactivated Faut-il essayer la branche "deactivated" si aucun résultat ?
     * @return array
     */
    public function findOneByFilter($filterName, $tryDeactivated = false)
    {
        $filter = sprintf($this->configParam('filters', 'FILTER_STRUCTURE_DN'), $filterName);
        $entry = $this->searchSimplifiedEntries($filter, $this->configParam('dn', 'STRUCTURES_BASE_DN'));
        if (!$entry && $tryDeactivated) {
            $entry = $this->searchSimplifiedEntries($filter, $this->configParam('dn', 'UTILISATEURS_DESACTIVES_BASE_DN'));
        }

        return $entry;
    }

    /**
     * Recherche une structure par son code entité Supann.
     *
     * @param string $codeEntite Supann code Entite
     * @return \UnicaenApp\Entity\Ldap\Structure
     */
    public function findOneByCodeEntite($codeEntite)
    {
        $filter = sprintf($this->configParam('filters', 'FILTER_STRUCTURE_CODE_ENTITE'), $codeEntite);
        $entry = $this->searchSimplifiedEntry($filter, $this->configParam('dn', 'STRUCTURES_BASE_DN'));

        return $entry ? new LdapStructureModel($entry) : null;
    }
}
