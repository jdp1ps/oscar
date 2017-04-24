<?php
namespace UnicaenLdap\Filter;

use Zend\Ldap\Filter\MaskFilter;
use UnicaenLdap\Entity\Structure as Structure;

/**
 * Filtres pour les personnes
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 */
class People extends Filter
{

    /**
     * Génère un filtre de recherche par numéro Harpège
     *
     * @param string|integer $numero    Numéro Harpère d'un individu
     * @return self
     */
    public static function noIndividu($numero)
    {
        return self::equals('supannEmpId', sprintf("%08s", intval($numero)));
    }

    /**
     * Génère un filtre de recherche par loin utilisateur
     *
     * @param string $login    Login utilisateur
     * @return self
     */
    public static function username($login)
    {
        return self::equals('supannAliasLogin', $login);
    }

    /**
     * Génère un filtre de recherche par nom d'utilisateur
     *
     * @param string $name    Login utilisateur
     * @return self
     */
    public static function name($name)
    {
        return self::equals('cn', $name);
    }

    /**
     * Génère un filtre de recherche par nom d'utilisateur ou par login utilisateur
     *
     * @param string $name    Login utilisateur ou nom d'utilisateur
     * @return self
     */
    public static function nameOrUsername($name)
    {
        return self::orFilter(
            self::username($name),
            self::name($name)
        );
    }

    /**
     * Génère un filtre de recherche par affectation
     *
     * @todo à terminer
     * @param string|EntityStructure $structure Structure
     * @return self
     */
    public static function affectation($name)
    {
        // (&(uid=*)(|(ucbnSousStructure=%s;*)(supannAffectation=%s;*)))
        //return self::equals('cn', $name);
        throw new Exception('Méthode non terminée');
    }

    /**
     * Génère un filtre de recherche selon leur appartenance à un groupe et leur structure d'affectation.
     *
     * @todo à terminer
     * @return self
     */
    public static function membership( $group, $structure = null, $recursive=false )
    {//(memberOf=%s)     (eduPersonOrgUnitDN=%s)
        // (&(uid=*)(|(ucbnSousStructure=%s;*)(supannAffectation=%s;*)))
        //return self::equals('cn', $name);
        throw new Exception('Méthode non terminée');
    }

    /**
     * Génère un filtre de recherche pour les rôles
     * 
     * @param string $role
     * @param string $type
     * @param string $structure
     * @return type
     */
    public static function role( $role = null, $type = null, $structure = null )
    {
	$mask = 'supannRoleEntite=[role={SUPANN}%s][type={SUPANN}%s][code=%s][libelle=*]';
	return self::string(
		    self::unescapeValue(
			new MaskFilter($mask, $role ?: '*', $type ?: '*', $structure ?: '*')
		    )
	       );
    }

    /**
     * Génère un filtre de recherche de professeurs par affectation
     *
     * @todo à terminer
     * @return self
     */
    public static function teachersByStructure( $structure )
    {//(&(eduPersonAffiliation=teacher)(eduPersonOrgUnitDN=%s))
        // (&(uid=*)(|(ucbnSousStructure=%s;*)(supannAffectation=%s;*)))
        //return self::equals('cn', $name);
        throw new Exception('Méthode non terminée');
    }

}