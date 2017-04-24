<?php
namespace UnicaenApp\Mapper\Ldap;

use UnicaenApp\Entity\Ldap\Structure as LdapStructureModel;

/**
 * Classe regroupant les opérations de recherche de structure dans l'annuaire LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Structure extends AbstractMapper
{
    const STRUCTURES_BASE_DN		      = 'ou=structures,dc=unicaen,dc=fr';
    const CHEMIN_INTROUVABLE		      = "(INTROUVABLE DANS ANNUAIRE)";
    const FILTER_STRUCTURE_DN		      = '(%s)';
    const FILTER_STRUCTURE_CODE_ENTITE	      = '(supannCodeEntite=%s)';
    const FILTER_STRUCTURE_CODE_ENTITE_PARENT = '(supannCodeEntiteParent=%s)';
    
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
     * Recherche une structure par son DN.
     * 
     * @param string $dn DN
     * @return \UnicaenApp\Entity\Ldap\Structure
     */
    public function findOneByDn($dn)
    {
        $filter = sprintf(self::FILTER_STRUCTURE_DN, trim(stristr($dn, self::STRUCTURES_BASE_DN, true), ','));
        $entry = $this->searchSimplifiedEntry($filter, self::STRUCTURES_BASE_DN);
        return $entry ? new LdapStructureModel($entry) : null;
    }
    
    /**
     * Recherche une structure par son code entité Supann.
     * 
     * @param string $codeEntite Supann code Entite
     * @return \UnicaenApp\Entity\Ldap\Structure
     */
    public function findOneByCodeEntite($codeEntite)
    {
        $filter = sprintf(self::FILTER_STRUCTURE_CODE_ENTITE, $codeEntite);
        $entry = $this->searchSimplifiedEntry($filter, self::STRUCTURES_BASE_DN);
        return $entry ? new LdapStructureModel($entry) : null;
    }
    
    /**
     * Recherche une structure par son DN ou son code entité Supann.
     * 
     * @param string $dnOrCodeEntite DN ou code entité Supann
     * @return \UnicaenApp\Entity\Ldap\Structure
     */
    public function findOneByDnOrCodeEntite($dnOrCodeEntite)
    {
        $isDn = count(ldap_explode_dn($dnOrCodeEntite, 1)) > 1;
        if ($isDn) {
            return $this->findOneByDn($dnOrCodeEntite);
        }
        else {
            return $this->findOneByCodeEntite($dnOrCodeEntite);
        }
    }
    
    /**
     * Recherche une structure par son code Harpege.
     *
     * @param string $codeStructure Code(s) structure(s) Harpege, ex: 'C68'
     * @return LdapStructureModel
     */
    public function findOneByCodeStructure($codeStructure = null)
    {
        $filter = LdapStructureModel::createFilterForStructure($codeStructure);
        $entry = $this->searchSimplifiedEntry($filter, self::STRUCTURES_BASE_DN);
        return $entry ? new LdapStructureModel($entry) : null;
    }
    
    /**
     * Recherche de structures par leur code Harpege.
     *
     * @param string|array $codeStructure Code(s) structure(s) Harpege, ex: 'C68'
     * @return array
     */
    public function findAllByCodeStructure($codeStructure = null)
    {
        $filter = LdapStructureModel::createFilterForStructure($codeStructure);
        $entries = $this->searchSimplifiedEntries($filter, self::STRUCTURES_BASE_DN, array(), array('supanncodeentiteparent','supanncodeentite'));
        return $entries ? LdapStructureModel::getInstances($entries) : array();
    }
    
//    /**
//     * Recherche une(des) structure(s) par son(leurs) code(s) Harpege.
//     *
//     * @param array|string $codeStructure Code(s) structure(s) Harpege, ex: 'C68'
//     * @param bool $objectify Transformer les entrées LDAP trouvées en objets
//     * @return LdapStructureModel|array
//     */
//    protected function _findAllByCodeStructure($codeStructure = null, $objectify = true)
//    {
//        $filter = LdapStructureModel::createFilterForStructure($codeStructure);
//
//        if (!$codeStructure || is_array($codeStructure)) {
//            $entries = $this->searchSimplifiedEntries(
//                    $filter,
//                    self::STRUCTURES_BASE_DN,
//                    array(),
//                    array('supanncodeentiteparent','supanncodeentite'));
//            $result = $objectify && $entries ? LdapStructureModel::getInstances($entries) : $entries;
//        }
//        else {
//            $entry = $this->searchSimplifiedEntry($filter, self::STRUCTURES_BASE_DN);
//            $result = ($objectify && $entry) ? new LdapStructureModel($entry) : $entry;
//        }
//
//        return $result;
//    }
    
    /**
     * Recherche les structures filles d'une structure par son code entité Supann.
     * 
     * @param string $codeEntite SupAnn code Entite
     * @param string $sort attribut de tri
     * @return array
     */
    public function findStructureChildsByCodeEntite($codeEntite, $sort = 'supanncodeentite')
    {
        if (!$codeEntite) {
            return array();
        }

        $sort = is_string($sort) ? $sort : 'supanncodeentite';

        $filter  = sprintf(self::FILTER_STRUCTURE_CODE_ENTITE_PARENT, $codeEntite);
        $entries = $this->searchSimplifiedEntries($filter, self::STRUCTURES_BASE_DN, array(), $sort);

        return $entries ? LdapStructureModel::getInstances($entries) : array();
    }

    /**
     * Recherche le chemin complet (hiérarchique) de plusieurs structures.
     *
     * @param string|array $codeStructure Code(s) Harpege
     * @param boolean $inclureRacine Inclure la racine (niveau 1)
     * @param boolean $libelleLong Utilser les libellés long des structures
     * @param boolean $appendCode Faut-il faire figurer le code de chaque structure
     * @param string $glue Séparateur
     * @return array
     */
    public function findAllPathByCodeStructure($codeStructure, $inclureRacine = false, $libelleLong = false, $appendCode = false, $glue = ' > ')
    {
        $entries = $this->findAllByCodeStructure($codeStructure);
        if (!$entries) {
            return self::CHEMIN_INTROUVABLE;
        }

        $result = array();
        foreach ($entries as $entry /* @var $entry LdapStructureModel */) {
            $code = $entry->getCStructure();
            $result[$code] = $this->findOnePathByCodeStructure($code, $inclureRacine, $libelleLong, $appendCode, $glue);
        }
        asort($result);

        return $result;
    }
    
    /**
     * Détermine le chemin complet (hiérarchique) d'une seule structure de l'établissement.
     * 
     * @param string $codeStructure Code Harpege de la structure
     * @param boolean $inclureRacine Faut-il que la racine (UNIVERSITE DE CAEN) figure dans le résultat ?
     * @param boolean $libelleLong Libellé long ou libellé court ?
     * @param boolean $appendCode Faut-il faire figurer le code de chaque structure
     * @param string $glue Séparateur à utiliser
     * @return string Ex (racine inclue): "UNIVERSITE DE CAEN > CRISI > CRISI-DIG"
     */
    public function findOnePathByCodeStructure($codeStructure, $inclureRacine = false, $libelleLong = false, $appendCode = false, $glue = ' > ')
    {
        $code = $codeStructure;
        $ch = array();
        do {
            if (!($str = $this->findOneByCodeStructure($code))) {
                return self::CHEMIN_INTROUVABLE;
            }
            $ch[] = $libelleLong ? $str->getDescription() : $str->getOu();
            $code = ($parent = $str->getSupannCodeEntiteParent()) ?
                    LdapStructureModel::extractCodeStructureHarpege($parent) :
                    null;
        }
        while (!empty($code));

        $ch = array_reverse($ch);
        if (!$inclureRacine && count($ch) > 1) {
            $ch = array_slice($ch, 1);
        }
        $ch = implode($glue, $ch);
        
        if ($appendCode) {
           $ch .= ' [' . $codeStructure . ']';
        }

        return $ch;
    }
}
