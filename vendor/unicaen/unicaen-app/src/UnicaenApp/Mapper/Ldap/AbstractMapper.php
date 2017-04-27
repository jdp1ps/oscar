<?php
namespace UnicaenApp\Mapper\Ldap;

use Zend\Ldap\Ldap;
use UnicaenApp\Exception\RuntimeException;

/**
 * Classe mère des services d'accès à l'annuaire LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
abstract class AbstractMapper
{
    /**
     * @var Ldap
     */
    protected $ldap;

    /**
     * Constructeur.
     *
     * @param Ldap $ldap Objet d'accès à l'annuaire LDAP
     */
    public function __construct(Ldap $ldap = null)
    {
        $this->setLdap($ldap);
    }
    
    /**
     * Retourne la liste des attributs LDAP remontés dans les résultats de recherches.
     * NB: l'attribut 'dn' est forcément inclus.
     * 
     * @return array e.g. array("mail", "sn", "cn")
     */
    abstract public function getAttributes();

    /**
     * Retourne l'objet d'accès à l'annuaire LDAP.
     *
     * @return Ldap
     */
    public function getLdap()
    {
        return $this->ldap;
    }
    
    /**
     * Spécifie l'objet d'accès à l'annuaire LDAP.
     *
     * @param Ldap $ldap
     * @return AbstractMapper
     */
    public function setLdap(Ldap $ldap = null)
    {
        $this->ldap = $ldap;
        return $this;
    }
    
    /**
     * Cherche une entrée dans l'annuaire LDAP selon un filtre puis parcourt récursivement le résultat trouvé
     * pour remplacer tout array qui ne contient qu'une seule valeur par cette valeur.
     * 
     * @param  string|Zend_Ldap_Filter_Abstract|array $filter
     * @param  string|Zend_Ldap_Dn|null               $basedn
     * @param  array                                  $attributes
     * @param  string|null                            $sort
     * @param  integer                                $scope
     * @return array
     */
    public function searchSimplifiedEntry($filter, $baseDn = null, array $attributes = array(), $sort = null, $scope = Ldap::SEARCH_SCOPE_SUB)
    {
        if (!$attributes) {
            $attributes = $this->getAttributes() ?: array('*');
        }
        $entries = $this->getLdap()->searchEntries($filter, $baseDn, $scope, $attributes, $sort);
        if (count($entries) > 1) {
            throw new RuntimeException("Plus d'une entrée trouvée avec ce filtre: " . $filter);
        }
        if (!$entries) {
            return null;
        }
        $entry = $entries[0];
        return self::simplifiedEntry($entry);
    }

    /**
     * Cherche des entrées dans l'annuaire LDAP selon un filtre puis parcourt récursivement les entrées trouvées
     * pour remplacer tout array qui ne contient qu'une seule valeur par cette valeur.
     *
     * @param  string|Zend_Ldap_Filter_Abstract|array $filter
     * @param  string|Zend_Ldap_Dn|null               $basedn
     * @param  array                                  $attributes
     * @param  string|null                            $sort
     * @param  integer                                $scope
     * @return array
     */
    public function searchSimplifiedEntries($filter, $basedn = null, array $attributes = array(), $sort = null, $scope = Ldap::SEARCH_SCOPE_SUB)
    {
        if (!$attributes) {
            $attributes = $this->getAttributes() ?: array('*');
        }
        $entries = $this->getLdap()->searchEntries($filter, $basedn, $scope, $attributes, $sort);
        foreach ($entries as $i => $entry) {
            $entries[$i] = self::simplifiedEntry($entry);
        }
        return $entries;
    }

    /**
     * Parcours récursivement le tableau spécifié pour remplacer tout array qui ne contient
     * qu'une seule valeur par cette valeur.
     * 
     * @param array $entry
     * @return array Le tableau "simplifié"
     */
    static public function simplifiedEntry(array $entry, array $returnAttribs = array(), array $omitAttribs = array())
    {
        $return = array();
        
        foreach ($entry as $attr => $value) {
            if (($returnAttribs && !in_array($attr, $returnAttribs)) || ($omitAttribs && in_array($attr, $omitAttribs))) {
                continue;
            }
            if (is_array($value)) {
                if (count($value) > 1) {
                    $return[$attr] = self::simplifiedEntry($value);
                }
                else {
                    $return[$attr] = count($value) === 1 ? $value[0] : array();
                }
            }
            else {
                $return[$attr] = $value;
            }
        }

        return $return;
    }
}
