<?php

namespace Oscar\Entity;

use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Mapper\Ldap\AbstractMapper;

/**
 * Classe regroupant les opérations de recherche d'individu dans l'annuaire LDAP.
 *
 * @author Unicaen
 */
class PersonLdap extends AbstractMapper
{
    /**
     * @var Structure
     */
    protected $mapperStructure;

    /**
     * Retourne la liste des attributs LDAP remontés dans les résultats de recherches.
     * NB: l'attribut 'dn' est forcément inclus.
     *
     * @return array e.g. array("mail", "sn", "cn")
     */
    public function getAttributes()
    {
        return ['*', 'memberOf'];
    }

    /**
     * Recherche une liste de personnes
     *
     * @param string $filterName    filter
     * @param bool   $tryDeactivated Faut-il essayer la branche "deactivated" si aucun résultat ?
     * @return array
     */
    public function findAll($filterName, $tryDeactivated = false)
    {
        $filter = sprintf($this->configParam('filters', 'FILTER_STRUCTURE_DN'), $filterName);
        $entry = $this->searchSimplifiedEntries($filter, $this->configParam('dn', 'UTILISATEURS_BASE_DN'));
        if (!$entry && $tryDeactivated) {
            $entry = $this->searchSimplifiedEntries($filter, $this->configParam('dn', 'UTILISATEURS_DESACTIVES_BASE_DN'));
        }

        return $entry;
    }

    /**
     * Spécifie l'objet d'accès aux structures LDAP.
     *
     * @param Structure $mapperStructure
     * @return self
     */
    public function setMapperStructure(Structure $mapperStructure)
    {
        $this->mapperStructure = $mapperStructure;
        if ($this->mapperStructure) {
            $this->mapperStructure->setLdap($this->ldap);
            $this->mapperStructure->setConfig($this->config);
        }

        return $this;
    }

    /**
     * Retourne l'objet d'accès aux structures LDAP.
     *
     * @return Structure
     */
    public function getMapperStructure()
    {
        if (null === $this->mapperStructure) {
            $this->mapperStructure = new Structure($this->ldap, $this->config);
        }

        return $this->mapperStructure;
    }
}
