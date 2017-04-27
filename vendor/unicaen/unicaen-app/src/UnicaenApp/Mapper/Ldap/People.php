<?php

namespace UnicaenApp\Mapper\Ldap;

use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;

/**
 * Classe regroupant les opérations de recherche d'individu dans l'annuaire LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class People extends AbstractMapper
{
    const UTILISATEURS_BASE_DN                = 'ou=people,dc=unicaen,dc=fr';
    const UTILISATEURS_DESACTIVES_BASE_DN     = 'ou=deactivated,dc=unicaen,dc=fr';
    const LOGIN_FILTER                        = '(supannAliasLogin=%s)';
    const UTILISATEUR_STD_FILTER              = '(|(uid=p*)(&(uid=e*)(eduPersonAffiliation=student)))';
    const NAME_FILTER                         = '(cn=%s*)';
    const UID_FILTER                          = '(uid=%s)';
    const NO_INDIVIDU_FILTER                  = '(supannEmpId=%08s)';
    const AFFECTATION_FILTER                  = '(&(uid=*)(eduPersonOrgUnitDN=%s))';
    const AFFECTATION_CSTRUCT_FILTER          = '(&(uid=*)(|(ucbnSousStructure=%s;*)(supannAffectation=%s;*)))';
    const LOGIN_OR_NAME_FILTER                = '(|(supannAliasLogin=%s)(cn=%s*))';
    const MEMBERSHIP_FILTER                   = '(memberOf=%s)';
    const AFFECTATION_ORG_UNIT_FILTER         = '(eduPersonOrgUnitDN=%s)';
    const AFFECTATION_ORG_UNIT_PRIMARY_FILTER = '(eduPersonPrimaryOrgUnitDN=%s)';
    const ROLE_FILTER                         = '(supannRoleEntite=[role={SUPANN}%s][type={SUPANN}%s][code=%s]*)';
    const PROF_STRUCTURE                      = '(&(eduPersonAffiliation=teacher)(eduPersonOrgUnitDN=%s))';
    
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
        return array('*', 'memberOf');
    }
            
    /**
     * Recherche un individu par son UID.
     * 
     * @param string $uid UID
     * @param bool $tryDeactivated Faut-il essayer la branche "deactivated" si aucun résultat ?
     * @return \UnicaenApp\Entity\Ldap\People
     */
    public function findOneByUid($uid, $tryDeactivated = false)
    {
        $filter = sprintf(self::UID_FILTER, $uid);
        $entry = $this->searchSimplifiedEntry($filter, self::UTILISATEURS_BASE_DN);
        if (!$entry && $tryDeactivated) {
            $entry = $this->searchSimplifiedEntry($filter, self::UTILISATEURS_DESACTIVES_BASE_DN);
        }
        if ($entry) {
            $entry = new \UnicaenApp\Entity\Ldap\People($entry);
        }
        return $entry;
    }
    
    /**
     * Recherche un individu par son numéro d'individu Harpege.
     *
     * @param integer $noIndividu Numéro d'individu Harpege
     * @param bool $tryDeactivated Faut-il essayer la branche "deactivated" si aucun résultat ?
     * @return \UnicaenApp\Entity\Ldap\People
     */
    public function findOneByNoIndividu($noIndividu, $tryDeactivated = false)
    {
        $filter = sprintf(self::NO_INDIVIDU_FILTER, $noIndividu);
        $entry = $this->searchSimplifiedEntry($filter, self::UTILISATEURS_BASE_DN);
        if (!$entry && $tryDeactivated) {
            $entry = $this->searchSimplifiedEntry($filter, self::UTILISATEURS_DESACTIVES_BASE_DN);
        }
        if ($entry) {
            $entry = new \UnicaenApp\Entity\Ldap\People($entry);
        }
        return $entry;
    }
    
    /**
     * Recherche d'individu par identifiant (login).
     *
     * @param string $login Login utilisateur
     * @param bool $tryDeactivated Faut-il essayer la branche "deactivated" si aucun résultat ?
     * @return \UnicaenApp\Entity\Ldap\People
     */
    public function findOneByUsername($login, $tryDeactivated = false)
    {
        $filter = sprintf(self::LOGIN_FILTER, $login);
        $entry = $this->searchSimplifiedEntry($filter, self::UTILISATEURS_BASE_DN);
        if (!$entry && $tryDeactivated) {
            $entry = $this->searchSimplifiedEntry($filter, self::UTILISATEURS_DESACTIVES_BASE_DN);
        }
        if ($entry) {
            $entry = new \UnicaenApp\Entity\Ldap\People($entry);
        }
        return $entry;
    }

    /**
     * Recherche d'individu par nom de famille et prénom.
     * 
     * @param string $name Tout ou partie du nom de famille et prénom
     * @param string $attributeForKey Attribut LDAP à utiliser comme clé du tableau de résultat
     * @param string $filterUtilisateur Filtre de recherche supplémentaire éventuel
     * @param bool $tryDeactivated Faut-il essayer la branche "deactivated" en plus ?
     * @return array
     */
    public function findAllByName($name, $attributeForKey = null, $filterUtilisateur = null, $tryDeactivated = false)
    {
        $nameFilter = sprintf(self::NAME_FILTER, $name);
        if (null === $filterUtilisateur || !is_string($filterUtilisateur)) {
            $filter = sprintf('(&%s%s)',
                    self::UTILISATEUR_STD_FILTER,
                    $nameFilter);
        }
        else {
            $filter = sprintf('(&%s%s)', $nameFilter, $filterUtilisateur);
        }
        $found = $this->searchSimplifiedEntries(
                $filter,
                self::UTILISATEURS_BASE_DN,
                array(),
               'cn');
        if ($tryDeactivated) {
            $foundDeactivated = $this->searchSimplifiedEntries(
                    $filter,
                    self::UTILISATEURS_DESACTIVES_BASE_DN,
                    array(),
                    'cn');
            $found = array_merge($found, $foundDeactivated);
        }

        $entries = array();
        foreach ($found as $k => $entry) {
            $k =  ($attributeForKey && !empty($entry[strtolower($attributeForKey)])) ? $entry[strtolower($attributeForKey)] : $entry['uid'];
            $entries[$k] = new \UnicaenApp\Entity\Ldap\People($entry);
        }
        
        return $entries;
    }
    
    /**
     * Recherche d'individus à partir du début du nom complet (ex: "gaut*", "gauthier ber*")
     * ou de l'identifiant 'supannAliasLogin' exact.
     *
     * @param string $nameOrUsername Nom ou login utilisateur
     * @param string $attributeForKey Attribut LDAP à utiliser comme clé du tableau de résultat
     * @param string $filterUtilisateur Filtre de recherche supplémentaire éventuel
     * @param bool $tryDeactivated Faut-il essayer la branche "deactivated" en plus ?
     * @return array
     */
    public function findAllByNameOrUsername($nameOrUsername, $attributeForKey = null, $filterUtilisateur = null, $tryDeactivated = false)
    {
        $usernameFilter = sprintf(self::LOGIN_OR_NAME_FILTER, $nameOrUsername, $nameOrUsername);
        if (null === $filterUtilisateur || !is_string($filterUtilisateur)) {
            $filter = sprintf('(&%s%s)',
                    self::UTILISATEUR_STD_FILTER,
                    $usernameFilter);
        }
        else {
            $filter = sprintf('(&%s%s)', $usernameFilter, $filterUtilisateur);
        }
        $found = $this->searchSimplifiedEntries(
                $filter,
                self::UTILISATEURS_BASE_DN,
                array(),
               'cn');
        if ($tryDeactivated) {
            $foundDeactivated = $this->searchSimplifiedEntries(
                    $filter,
                    self::UTILISATEURS_DESACTIVES_BASE_DN,
                    array(),
                    'cn');
            $found = array_merge($found, $foundDeactivated);
        }
        
        $entries = array();
        foreach ($found as $k => $entry) {
            $k =  ($attributeForKey && !empty($entry[strtolower($attributeForKey)])) ? $entry[strtolower($attributeForKey)] : $entry['uid'];
            $entries[$k] = new \UnicaenApp\Entity\Ldap\People($entry);
        }
        
        return $entries;
    }

    /**
     * Recherche des individus affectés administrativement à une structure Harpege.
     *
     * @param string|array|\UnicaenApp\Entity\Ldap\Structure $structure Structure(s) concernée(s)
     * @param string $attributeForKey Attribut LDAP à utiliser comme clé du tableau de résultat
     * @param array $attributes Seuls attributs LDAP à remonter
     * @param bool $tryDeactivated Faut-il essayer la branche "deactivated" en plus ?
     * @return array Objets de type \UnicaenApp\Entity\Ldap\People
     */
    public function findAllByAffectation($structure, $attributeForKey = null, $attributes = array(), $tryDeactivated = false)
    {
        if (!is_array($structure)) {
            $structure = array($structure);
        }

        $sortAttribute = 'cn';

        if (!$attributeForKey) {
            $attributeForKey = 'uid';
        }
        if ($attributes && !in_array($attributeForKey, (array)$attributes)) {
            $attributes[] = $attributeForKey;
        }
        if ($attributes && !in_array($sortAttribute, (array)$attributes)) {
            $attributes[] = $sortAttribute;
        }
        
        $structures = array();
        foreach ($structure as $s) {
            if (!is_object($s)) {
                // 1ere tentative : on suppose que la structure est spécifiée par un code entité Supann (ex: 'HS_C68')
                $code = count($tmp = ldap_explode_dn($s, 1)) > 1 ? $tmp[0] : $s;
                $s = $this->getMapperStructure()->findOneByCodeEntite($code);
                // 2e tentative : on suppose que la structure est spécifiée par un code Harpege (ex: 'C68')
                if (!$s && !($s = $this->getMapperStructure()->findOneByCodeStructure($code))) {
                    throw new RuntimeException("Structure introuvable, '$code' n'est ni un code entité Supann ni un code Harpege valide.");
                }
            }
            $structures[] = $s;
        }
        
        $filter = self::createFilterForAffectation($structures);
        
        $found = $this->searchSimplifiedEntries(
                $filter,
                self::UTILISATEURS_BASE_DN,
                $attributes,
               'cn');
        if ($tryDeactivated) {
            $foundDeactivated = $this->searchSimplifiedEntries(
                    $filter,
                    self::UTILISATEURS_DESACTIVES_BASE_DN,
                    $attributes,
                'cn');
            $found = array_merge($found, $foundDeactivated);
        }

        $entries = array();
        foreach ($found as $k => $entry) {
            $k =  ($attributeForKey && !empty($entry[strtolower($attributeForKey)])) ? $entry[strtolower($attributeForKey)] : $entry['uid'];
            $entries[$k] = new \UnicaenApp\Entity\Ldap\People($entry);
        }
        
        return $entries;
    }
    
    /**
     * Recherche d'individus selon leur appartenance à un groupe et leur structure d'affectation.
     * 
     * @param string|\UnicaenApp\Entity\Ldap\Group $memberOf DN ou entité
     * @param string|\UnicaenApp\Entity\Ldap\Structure $structure
     * @param bool $recursive Remonter la hiérarchie des structures tant qu'aucun membre n'est trouvé ?
     * @return \UnicaenApp\Entity\Ldap\People[]
     */
    public function findAllByMembership($memberOf, $structure = null, $recursive = false)
    {
        if (!$structure) {
            $recursive = false;
        }
        
        if ($memberOf instanceof \UnicaenApp\Entity\Ldap\Group) {
            $memberOf = $memberOf->getDn();
        }
        $memberFilter = sprintf(self::MEMBERSHIP_FILTER, $memberOf);
        
        // on s'assure que la structure éventuelle est au format objet
        if (is_string($structure)) {
            if (!($structure = $this->getMapperStructure()->findOneByDnOrCodeEntite($id = $structure))) {
                throw new RuntimeException("Structure introuvable dans l'annuaire avec le DN ou code entité '$id'.");
            }
            /* @var $structure \UnicaenApp\Entity\Ldap\Structure */
        }
        
        // en mode "récursif", on remonte la hiérarchie des structures tant qu'aucun membre n'est trouvé
        do {
            $filter = $memberFilter;
            if ($structure) {
                $filter = sprintf('(&%s%s)', $memberFilter, sprintf(self::AFFECTATION_ORG_UNIT_FILTER, $structure->getDn()));
            }
            $found = $this->searchSimplifiedEntries(
                    $filter,
                    self::UTILISATEURS_BASE_DN,
                    array(),
                    'cn');
            // on remonte la hiérarchie des structures jusqu'à la racine
            if ($recursive && $structure && ($codeEntiteParent = $structure->getSupannCodeEntiteParent())) {
                $structure = $this->getMapperStructure()->findOneByCodeEntite($codeEntiteParent);
            }
            if (!$structure) {
                break;
            }
        } 
        while ($recursive && !$found);

        $entries = array();
        foreach ($found as $k => $entry) {
            $k = $entry['uid'];
            $entries[$k] = new \UnicaenApp\Entity\Ldap\People($entry);
        }
        
        return $entries;
    }
    
    /**
     * Recherche d'individus selon leur appartenance à un groupe et leur structure d'affectation.
     * 
     * @param string $role Ex: 'T84' (Chargé de la Sécurité des Systèmes d'Information)
     * @param string|\UnicaenApp\Entity\Ldap\Structure $structure DN, code entité Supann ou objet
     * @param bool $recursive
     * @return \UnicaenApp\Entity\Ldap\People[]
     */
    public function findAllByRole($role, $structure = null, $recursive = false)
    {
        if (!$structure) {
            $recursive = false;
        }
        
        $codeEntite = '*';
        
        // on s'assure que la structure éventuelle est au format objet
        if ($structure) {
            if (is_string($structure)) {
                if (!($structure = $this->getMapperStructure()->findOneByDnOrCodeEntite($id = $structure))) {
                    throw new RuntimeException("Structure introuvable dans l'annuaire avec le DN ou code entité '$id'.");
                }
            }
            $codeEntite = $structure->getSupannCodeEntite();
        }
        /* @var $structure \UnicaenApp\Entity\Ldap\Structure */
        
        do {
            $filter = sprintf(self::ROLE_FILTER, $role, '*', $codeEntite);
            
            $found = $this->searchSimplifiedEntries(
                    $filter,
                    self::UTILISATEURS_BASE_DN,
                    array(),
                    'cn');
            // on remonte la hiérarchie des structures jusqu'à la racine
            if ($recursive && $structure && ($codeEntite = $structure->getSupannCodeEntiteParent())) {
                $structure = $this->getMapperStructure()->findOneByCodeEntite($codeEntite);
            }
            if (!$structure) {
                break;
            }
        } 
        while ($recursive && !$found);

        $entries = array();
        foreach ($found as $k => $entry) {
            $k = $entry['uid'];
            $entries[$k] = new \UnicaenApp\Entity\Ldap\People($entry);
        }
        
        return $entries;
    }
    
    /**
     * Recherche de professeurs par affectation.
     * 
     * @param string|\UnicaenApp\Entity\Ldap\Structure $structure DN, code entité Supann ou objet
     * @return \UnicaenApp\Entity\Ldap\People[]
     */
    public function findAllTeachersByStructure($structure)
    {
        // on s'assure que la structure est au format objet
        if (is_string($structure)) {
            if (!($structure = $this->getMapperStructure()->findOneByDnOrCodeEntite($id = $structure))) {
                throw new RuntimeException("Structure introuvable dans l'annuaire avec le DN ou code entité '$id'.");
            }
        }
        /* @var $structure \UnicaenApp\Entity\Ldap\Structure */
        
        $filter = sprintf(self::PROF_STRUCTURE, $structure->getDn());
        $found = $this->searchSimplifiedEntries(
                $filter,
                self::UTILISATEURS_BASE_DN,
                array(),
                'cn');

        $entries = array();
        foreach ($found as $k => $entry) {
            $k = $entry['uid'];
            $entries[$k] = new \UnicaenApp\Entity\Ldap\People($entry);
        }
        
        return $entries;
    }

    /**
     * Crée le filtre LDAP qui va bien pour rechercher par structure d'affectation.
     *
     * @param string|array|\UnicaenApp\Entity\Ldap\Structure $structure Structure(s) concernée(s)
     * @return string
     */
    static public function createFilterForAffectation($structure)
    {
        $filter = array();
        foreach ((array)$structure as $s) {
            if ($s instanceof \UnicaenApp\Entity\Ldap\Structure) {
                $s = $s->getCStructure();
            }
            $filter[] = sprintf(self::AFFECTATION_CSTRUCT_FILTER, $s, $s);
        }
        if (count($filter) === 1) {
            return $filter[0];
        }
        $filter = '(|' . implode('', $filter) . ')';
        return $filter;
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
            $this->mapperStructure->setLdap($this->getLdap());
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
            $this->mapperStructure = new Structure($this->getLdap());
        }
        return $this->mapperStructure;
    }
}
