<?php
namespace UnicaenApp\Entity\Ldap;

use InvalidArgumentException;
use UnicaenApp\Exception\MandatoryValueException;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;

/**
 * Classe de représentation d'un individu de l'annuaire LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class People extends AbstractEntity
{
    static protected $pattern = '/^\[role={SUPANN}(.*)\]\[type={SUPANN}(.*)\]\[code=(.*)\]\[libelle=(.*)\]$/';
    
    /**
     * Rôle de responsable de la sécurité des systèmes d'information
     */
    const ROLE_RSSI = 'T83';
    /**
     * Rôle de correspondant sécurité des systèmes d'information
     */
    const ROLE_CSSI = 'T84';
    
    protected $cn;
    protected $datedenaissance;
    protected $displayname;
    protected $dn;
    protected $givenname;
    protected $mail;
    protected $postaladdress;
    protected $sexe;
    protected $sn;
    protected $supannaffectation;
    protected $ucbnstructurerecherche;
    protected $eduPersonOrgUnitDN;
    protected $eduPersonPrimaryOrgUnitDN;
    protected $supannaliaslogin;
    protected $supanncivilite;
    protected $supannempid;
    protected $telephonenumber;
    protected $ucbnsousstructure;
    protected $ucbnfonctionstructurelle;
    protected $supannroleentite;
    protected $ucbnsitelocalisation;
    protected $ucbnstatus;
    protected $uid;
    protected $uidnumber;
    protected $memberof;
    
    protected $memberOfGroup;
    protected $estDesactive;
    
    /**
     * Spécifie les valeurs des attributs de cet individu LDAP.
     *
     * @param array $data Données brutes
     * @return self
     */
    public function setData(array $data = array())
    {
        $this->data = $data;
        
        try {
            $this->dn                        = $this->processDataValue('dn', true);
            $this->cn                        = $this->processDataValue('cn');
            $this->datedenaissance           = $this->processDataValue('datedenaissance');
            $this->displayname               = $this->processDataValue('displayname');
            $this->givenname                 = $this->processDataValue('givenname');
            $this->mail                      = $this->processDataValue('mail');
            $this->postaladdress             = $this->processDataValue('postaladdress');
            $this->sexe                      = $this->processDataValue('sexe');
            $this->sn                        = $this->processDataValue('sn');
            $this->supannaffectation         = $this->processDataValue('supannaffectation');
            $this->ucbnstructurerecherche    = $this->processDataValue('ucbnstructurerecherche');
            $this->eduPersonOrgUnitDN        = $this->processDataValue('edupersonorgunitdn');
            $this->eduPersonPrimaryOrgUnitDN = $this->processDataValue('edupersonprimaryorgunitdn');
            $this->ucbnstructurerecherche    = $this->processDataValue('ucbnstructurerecherche');
            $this->supannaliaslogin          = $this->processDataValue('supannaliaslogin');
            $this->supanncivilite            = $this->processDataValue('supanncivilite');
            $this->supannempid               = $this->processDataValue('supannempid');
            $this->telephonenumber           = $this->processDataValue('telephonenumber');
            $this->ucbnsousstructure         = $this->processDataValue('ucbnsousstructure');
            $this->ucbnfonctionstructurelle  = $this->processDataValue('ucbnfonctionstructurelle');
            $this->supannroleentite          = $this->processDataValue('supannroleentite');
            $this->ucbnsitelocalisation      = $this->processDataValue('ucbnsitelocalisation');
            $this->ucbnstatus                = $this->processDataValue('ucbnstatus');
            $this->uid                       = $this->processDataValue('uid');
            $this->uidnumber                 = $this->processDataValue('uidnumber');
            $this->memberof                  = $this->processDataValue('memberof');
        }
        catch (MandatoryValueException $mve) {
            throw new InvalidArgumentException("Les données fournies sont invalides.", null, $mve);
        }
        
        $this->estDesactive = in_array('deactivated', ldap_explode_dn($this->dn, 1));
        
	return $this;
    }

    /**
     * Retourne true si l'argument est au format "supannRoleEntite".
     * 
     * @param string $role
     * @return bool
     */
    static public function isSupannRoleEntite($string, &$role = null, &$typeStructure = null, &$codeStructure = null, &$libelleRole = null)
    {
        if (preg_match(static::$pattern, $string, $matches)) {
            $role          = $matches[1];
            $typeStructure = $matches[2];
            $codeStructure = $matches[3];
            $libelleRole   = $matches[4];
            return true;
        }
        return false;
    }

    public function getCn()
    {
        return $this->cn;
    }

    public function getDateDeNaissance()
    {
        return $this->datedenaissance;
    }

    public function getDisplayName()
    {
        return $this->displayname;
    }

    public function getDn()
    {
        return $this->dn;
    }

    public function getGivenName()
    {
        return $this->givenname;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function getPostalAddress()
    {
        return $this->postaladdress;
    }

    public function getSexe()
    {
        return $this->sexe;
    }

    public function getSn($nomUsuelUniquement = false)
    {
        return is_array($this->sn) && $nomUsuelUniquement ? current($this->sn) : $this->sn;
    }

    public function getNomUsuel()
    {
        return $this->getSn(true);
    }

    public function getNomPatronymique()
    {
        return is_array($sn = $this->getSn()) && isset($sn[1]) ? $sn[1] : $sn;
    }

    public function getSupannAffectation()
    {
        throw new \BadMethodCallException("Ne plus utiliser cette méthode!");
    }

    public function getUcbnStructureRecherche()
    {
        return $this->ucbnstructurerecherche;
    }

    public function getSupannAliasLogin()
    {
        return $this->supannaliaslogin;
    }

    public function getEduPersonOrgUnitDN()
    {
        return $this->eduPersonOrgUnitDN;
    }

    public function getEduPersonPrimaryOrgUnitDN()
    {
        return $this->eduPersonPrimaryOrgUnitDN;
    }

    public function getSupannCivilite()
    {
        return $this->supanncivilite;
    }

    public function getSupannEmpId()
    {
        return $this->supannempid;
    }

    public function getTelephoneNumber()
    {
        return $this->telephonenumber;
    }

    public function getUcbnSousStructure()
    {
        return $this->ucbnsousstructure;
    }

    public function getUcbnFonctionStructurelle()
    {
        return $this->ucbnfonctionstructurelle;
    }

    public function getSupannRoleEntite()
    {
        return $this->supannroleentite;
    }

    public function getUcbnSiteLocalisation()
    {
        return $this->ucbnsitelocalisation;
    }

    public function getUcbnStatus()
    {
        return $this->ucbnstatus;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getUidNumber()
    {
        return $this->uidnumber;
    }

    public function getEstDesactive()
    {
        return $this->estDesactive;
    }

    public function getMemberOf()
    {
        return (array)$this->memberof;
    }

    /**
     * Indique si cet individu LDAP est "désactivé" (i.e. dans la branche 'deactivated').
     *
     * @return bool
     */
    public function estDesactive()
    {
	return $this->getEstDesactive();
    }

    /**
     * Retourne les groupes auxquels appartient cet individu.
     * 
     * @param \UnicaenApp\Mapper\Ldap\Group $mapper Utilisé pour rechercher les entités correspondant aux 'cn'
     * @param \DateTime $dateObservation Spécifie la date d'observation des groupes (autrement dit,
     * si la date de fin du groupe est antérieure à la date d'observation, le groupe n'est pas retenu)
     * @return array(cn => Group)
     */
    public function getMemberOfGroup(\UnicaenApp\Mapper\Ldap\Group $mapper, \DateTime $dateObservation = null)
    {
        if (null === $this->memberOfGroup || $dateObservation) {
            $this->memberOfGroup = array();
            foreach ((array)$this->memberof as $dn) {
                $cn = ldap_explode_dn($dn, 1);
                $cn = next($cn);
                if (!($group = $mapper->findOneByCn($cn))) {
                    throw new RuntimeException("Groupe LDAP introuvable avec le CN '$cn'.");
                }
                if ($dateObservation && $dateObservation > $group->getSupannGroupeDateFin()) {
                    continue;
                }
                $this->memberOfGroup[$group->getDn()] = $group;
            }
        }
        return $this->memberOfGroup;
    }
    
    /**
     * Teste si cet individu est membre d'un ou plusieurs groupes.
     * 
     * @param array(cn)|array(Group)|string|Group $group Groupe(s) à tester
     * @param \DateTime $dateObservation Spécifie la date d'observation des groupes (autrement dit,
     * si la date de fin du groupe est antérieure à la date d'observation, le groupe n'est pas retenu)
     * @param \UnicaenApp\Mapper\Ldap\Group $mapper Utilisé pour rechercher les entités correspondant aux 'cn',
     * requis si une date d'observation est spécifiée
     * @return bool
     */
    public function isMemberOf($group, \DateTime $dateObservation = null, \UnicaenApp\Mapper\Ldap\Group $mapper = null)
    {
        if (!is_array($group)) {
            $group = array($group);
        }
            
        // si une date d'observation est spécifiée, on ne retient que les groupes en cours de validité (cf. date de fin)
        if ($dateObservation) {
            // si les groupes sont spécifiés par des CN ou DN, on les fetch pour pouvoir les filtrer selon leur date de fin
            if (!is_object(current($group))) {
                if (null === $mapper) {
                    throw new LogicException(
                            "Le mapper est requis car une date d'observation a été spécifiée et les groupes fournis ne sont pas des entités.");
                }
                foreach ($group as $key => $cnOuDn) {
                    $cn = count($tmp = ldap_explode_dn($cnOuDn, 1)) > 1 ? $tmp[0] : $cnOuDn;
                    $group[$key] = $cn;
                }
                $group = $mapper->findAllByCn($group); // array(dn=>Group)
            }
            // filtrage
            $group = array_keys(\UnicaenApp\Mapper\Ldap\Group::filterGroupsByDateFin($group, $dateObservation, $mapper));
            if (!$group) {
                return false;
            }
        }
        
        // si les groupes sont spécifiés par des entités, on récupère leur DN
        if (is_object(current($group))) {
            foreach ($group as $key => $g) {
                $group[$key] = $g->getDn();
            }
        }
        
        return count(array_intersect($group, $this->getMemberOf())) === count($group);
    }
    
    /**
     * Retourne la représentation littérale par défaut de cet objet.
     *
     * @return string
     */
    public function __toString()
    {
	return $this->getNomComplet(true);
    }

//    /**
//    ABANDONNÉ CAR NECESSITE LE MAPPER PEOPLE...
//    
//     * Retourne des infos complémentaires concernant cet individu LDAP :
//     * affectation, mail, identifiant persopass, témoin de compte désactivé.
//     *
//     * @param bool $affectations Inclure l'affectation ?
//     * @param bool $mail Inclure l'adresse mail ?
//     * @param bool $login Inclure l'affectation'identifiant persopass ?
//     * @return string
//     */
//    public function getInfosCompl($affectations = true, $mail = true, $login = true)
//    {
//        $pieces = array();
//        if ($affectations) {
//            $pieces[] = ($affs = $this->getAffectationsAdmin()) ? implode(' ; ', $affs) : 'Aucune affectation trouvée';
//        }
//        if ($mail) {
//            $pieces[] = $this->mail;
//        }
//        if ($login) {
//            $pieces[] = $this->supannaliaslogin;
//        }
//        if ($this->estDesactive()) {
//            $pieces[] = 'Compte DÉSACTIVÉ';
//        }
//	return implode(" - ", $pieces);
//    }

    /**
     * Retourne le nom complet de cet individu LDAP.
     * 
     * @param boolean $nomEnMajuscule Mettre le nom de famille en majuscules ?
     * @param boolean $avecCivilite Inclure la civilité ?
     * @param boolean $prenomDabord Mettre le prénom avant le nom de famille ?
     * @return string
     */
    public function getNomComplet($nomEnMajuscule = false, $avecCivilite = false, $prenomDabord = false)
    {
        if (!$this->getSn()) {
            return '';
        }
        
	$nom = $nomEnMajuscule ? strtoupper($this->getSn(true)) : current((array)$this->getSn(true));
	$prenom = $this->givenname;
	$civilite = $avecCivilite ? ' ' . $this->supanncivilite : null;

	return ($prenomDabord ? $prenom . ' ' . $nom : $nom . ' ' . $prenom) . $civilite;
    }

    /**
     * Retourne <code>true</code> si cet individu est un étudiant, <code>false</code> sinon.
     * 
     * @return bool
     */
    public function estEtudiant()
    {
        return "e" === substr($this->getUid(), 0, 1);
    }
    
    /**
     * Retourne les structures d'affectation de cet individu LDAP.
     *
     * @param \UnicaenApp\Mapper\Ldap\Structure $mapper Objet d'accès aux structures LDAP
     * @param boolean $primary Indique s'il faut ne prendre en compte que l'affectation principale
     * @param string $path Retourner le chemin hiérarchique de chaque affectation trouvée 
     * @return array code Harpege => Structure
     */
    public function getAffectationsAdmin(\UnicaenApp\Mapper\Ldap\Structure $mapper, $primary = false, $path = true)
    {
        $affectations = $primary ? $this->getEduPersonPrimaryOrgUnitDN() : $this->getEduPersonOrgUnitDN();
        
	$affs = array();
	foreach ((array)$affectations as $aff) {
            if (($ldapStr = $mapper->findOneByDn($aff))) {
                $key = $ldapStr->getCStructure();
                $lib = $path ? $mapper->findOnePathByCodeStructure($key) : $ldapStr->getDn();
                $affs[$key] = $lib;
            }
	}

	return $affs;
    }

    /**
     * Retourne les structures d'affectation "recherche" de cet individu LDAP.
     *
     * @param \UnicaenApp\Mapper\Ldap\Structure $mapper Objet d'accès aux structures LDAP
     * @param string $path Retourner le chemin hiérarchique de chaque affectation trouvée 
     * @return array Format: Code Harpege => Libellé structure
     */
    public function getAffectationsRecherche(\UnicaenApp\Mapper\Ldap\Structure $mapper, $path = true)
    {
	$ucbnstructurerecherche = array();

	if (isset($this->ucbnstructurerecherche)) {
	    $ucbnstructurerecherche = $this->ucbnstructurerecherche;
	}

	if (!is_array($ucbnstructurerecherche)) {
	    $ucbnstructurerecherche = array($ucbnstructurerecherche);
	}

	$affs = array();
	foreach ($ucbnstructurerecherche as $aff) {
	    list($code, $lib) = explode(';', $aff, 2);
            if (($ldapStr = $mapper->findOneByCodeStructure($code))) {
                $lib = $path ? $mapper->findOnePathByCodeStructure($code) : $ldapStr->getDn();
                $affs[$code] = $lib;
            }
	}

	return $affs;
    }

    /**
     * Retourne les structures d'affectation "recherche" de cet individu LDAP.
     *
     * @param \UnicaenApp\Mapper\Ldap\Structure $mapper Objet d'accès aux structures LDAP
     * @param string $path Retourner le chemin hiérarchique de chaque structure de responsabilité
     * @return array Format: Code Harpege => Libellé structure de responsabilité
     */
    public function getFonctionsStructurelles(\UnicaenApp\Mapper\Ldap\Structure $mapper, $path = true)
    {
        $rolesArray = $this->getSupannRolesEntiteToArray(null, true); // format condensé
        $fss = array();
        if ($rolesArray) {
            foreach ($rolesArray as $codeEntite => $roles) {
                if (($ldapStr = $mapper->findOneByCodeEntite($codeEntite))) {
                    $libStr = $path ? $mapper->findOnePathByCodeStructure($ldapStr->getCStructure()) : $ldapStr->getLcStructure();
                    foreach ($roles as /*$codeRole =>*/ $libRole) {
                        $fss[$codeEntite][] = $libRole . " ($libStr)";
                    }
                    $fss[$codeEntite] = implode(' ; ', $fss[$codeEntite]);
                }
            }
            ksort($fss);
        }

	return $fss;
    }

    /**
     * Retourne les rôles des personnes
     * Les rôles définis associent le code fonction d'une personne, le code du type de l'entité et le code entité.
     * Ex. [role={SUPANN}R00][type={SUPANN}S312][code=HS_S231]
     * Si une liste de rôle est fournie, il renvoie les rôles de la personne présents dans cette liste.
     * @access pubic
     * @param string|array liste des rôles qui nous intéressent
     * Format d'un rôle fourni : <c_role>[,;]<c_type_struct>[,;]<c_struct>
     * Ex. R00,S312,HS_S231
     *       D30;S231;HS_U01
     * @return array liste de rôles
     */
    public function getSupannRolesEntite($roles = null)
    {
	if (isset($this->supannroleentite)) {
	    $supannroleentite = (array)$this->supannroleentite;
	} 
        else {
            $supannroleentite = null;
        }

	if (!$supannroleentite) {
	    return array();
        }

	if (!$roles) {
	    return $supannroleentite;
        }
	if (!is_array($roles)) {
	    $roles = array($roles);
        }

	$tab_roles = array();
	foreach ($supannroleentite as $re) {
	    preg_match(static::$pattern, $re, $regs);
	    $bool = false;
	    foreach ($roles as $ro) {
		$ro_exp = preg_split('/[,;]/', $ro);
		// Code role
		if ($ro_exp[0] == $regs[1]) {
		    $bool = true;
                }
		else {
		    continue;
                }
		// Code type structure
		if (!empty($ro_exp[1]) && $ro_exp[1] != $regs[2]) {
		    $bool = false;
                }
		// Code structure
		if (!empty($ro_exp[2]) && $ro_exp[2] != $regs[3]) {
		    $bool = false;
                }

		if ($bool) {
		    array_push($tab_roles, $re);
		    break;
		}
	    }
	}

	return $tab_roles;
    }

    /**
     * Retourne les rôles des personnes sous forme de tableau
     * Les rôles définis associent le code fonction d'une personne, le code du type de l'entité et le code entité.
     * Ex.  tab[role]=R00
     *      tab[type]=S312
     *      tab[code]=HS_S231
     * Si une liste de rôle est fournie, il renvoie les rôles de la personne présents dans cette liste sous forme de tableau.
     * @access pubic
     * @param string|array liste des rôles qui nous intéressent
     * Format d'un rôle fourni : <c_role>[,;]<c_type_struct>[,;]<c_struct>
     * Ex. R00,S312,HS_S231
     *       D30;S231;HS_U01
     * @param string|array $roles
     * @param bool $condensed Mettre le résultat au format condensé ?
     * @return array Ex: 0 => array('role'=>'R40', 'type'=>'S312', 'code'=>'HS_S231'), 1 => ... au format normal ; 
     *                   'HS_S231' => array(0=>'R40'), 'HS_G72' => ... au format "condensé".
     */
    public function getSupannRolesEntiteToArray($roles = null, $condensed = false)
    {
        $tab_roles = array();
        $r = $this->getSupannRolesEntite($roles);
        foreach ($r as $role) {
            //on explose sur [
            $exp_role = explode('[', $role);
            for ($i=1; $i < count($exp_role); $i++) {
                //on supprime le caractère ]
                $exp_r = str_replace(']', '', $exp_role[$i]);
                //on explose sur =
                $tab = explode('=', $exp_r);
                //on incrémente  le tableau final en supprimant {SUPANN} si nécessaire
                $tab_r[$tab[0]] = str_replace('{SUPANN}', '', $tab[1]);
            }
            array_push($tab_roles, $tab_r);            
        }
        if ($condensed) {
            $array = array();
            foreach ($tab_roles as $r) {
                $array[$r['code']][$r['role']] = $r['libelle'];
            }
            $tab_roles = $array;
        }
        return $tab_roles;
    }

    /**
     * Méthode statique pour tri croissant d'individus selon leur 'cn'.
     *
     * @param People $a Individu LDAP
     * @param People $b Individu LDAP
     * @return Résultat du <code>strcasecmp</code> des 'cn'
     */
    static public function strcasecmpLdapPeople(People $a, People $b)
    {
        if (!$a->getCn() || !$b->getCn()) {
            throw new InvalidArgumentException("Les deux entités à comparer doivent posséder un 'cn' non vide.");
        }
	return strcasecmp($a->getCn(), $b->getCn());
    }
}