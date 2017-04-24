<?php
namespace UnicaenLdap\Entity;

use UnicaenLdap\Entity\Group;
use UnicaenLdap\Entity\Structure;
use DateTime;


/**
 * Classe mère des people de l'annuaire LDAP.
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class People extends Entity
{
    protected $role_pattern = '/^\[role={SUPANN}(.*)\]\[type={SUPANN}(.*)\]\[code=(.*)\]\[libelle=(.*)\]$/';

    /**
     * Liste des rôles existants
     * 
     * @var string[]
     */
    public static $roles_list = array(
	'DIRECTEUR'		=> 'D30',
	'RESPONSABLE'		=> 'R00',
	'RESP_ADMINISTRATIF'	=> 'R40',
    );

    protected $type = 'People';
    
    /**
     * Liste des classes d'objet nécessaires à la création d'une personne
     * Il est nécessaire d'ajouter la classe 'ucbnEtu' ou 'ucbnEmp' selon le
     * statut de la personne.
     * 
     * @var string[] 
     */
    protected $objectClass = array(
	'top',
	'inetOrgPerson',
	'organizationalPerson',
	'person',
	'eduPerson',
	'supannPerson',
	'sambaAccount',
	'sambaSamAcount',
	'posixAccount'
    );

    /**
     * Liste des attributs contenant des dates
     *
     * @var string[]
     */
    protected $dateTimeAttributes = array(

    );

    /**
     * Détermine si l'individu est actif ou non
     *
     * @return boolean
     */
    public function isPeople()
    {
        return 'people' == $this->getOu();
    }

    /**
     * Détermine si l'individu est désactivé ou non
     *
     * @return boolean
     */
    public function isDeactivated()
    {
        return 'deactivated' == $this->getOu();
    }

    /**
     * Détermine si l'individu est bloqué ou non
     *
     * @return boolean
     */
    public function isBlocked()
    {
        return 'blocked' == $this->getOu();
    }

    /**
     * Retourne l'Organizational Unit (OU) de l'utilisateur
     *
     * @return string
     */
    public function getOu()
    {
        if ($result = $this->getNode()->getDn()->get(1)){
            return $result['ou'];
        }
        return null;
    }

    /**
     * Retourne la liste des groupes dont l'utilisateur fait partie
     * Si le groupe n'est plus valide à la date d'observation, alors il n'est pas retourné dans la liste
     *
     * @param DateTime $dateObservation
     * @param string $orderBy Champ de tri (au besoin)
     * @return Group[]
     */
    public function getGroups( DateTime $dateObservation = null, $orderBy=null )
    {
        $group = $this->getService()->getServiceManager()->get('ldapServiceGroup');
        return $group->filterValids( $group->getAllBy( $this->get('memberOf'), 'dn', $orderBy ), $dateObservation );
    }

    /**
     * Détermine si la personne est étudiante
     *
     * @return boolean
     */
    public function estEtudiant()
    {
        return 0 === strpos($this->uid, 'e');
    }

    /**
     * Détermine si la personne est un personnel
     *
     * @return boolean
     */
    public function estPersonnel()
    {
        return 0 === strpos($this->uid, 'p');
    }

    /**
     * Détermine si la personne est un invité
     *
     * @return boolean
     */
    public function estInvite()
    {
        return 0 === strpos($this->uid, 'i');
    }

    /**
     * Retourne les structures auxquelles appartiennent la personne
     *
     * @return Structure[]
     */
    public function getEduPersonOrgUnit()
    {
        $structure = $this->getService()->getServiceManager()->get('ldapServiceStructure');
        $dn = $this->eduPersonOrgUnitDN;
        if (empty($dn)) return null;
        return $structure->getAllBy( $dn, 'dn' );
    }

    /**
     * Retourne la structure principale à laquelle appartient la personne
     *
     * @return Structure
     */
    public function getEduPersonPrimaryOrgUnit()
    {
        $structure = $this->getService()->getServiceManager()->get('ldapServiceStructure');
        $dn = $this->eduPersonPrimaryOrgUnitDN;
        if (empty($dn)) return null;
        return $structure->getBy( $dn, 'dn' );
    }

    /**
     * Retourne la structure d'affectation de la personne
     * @todo à terminer
     * @return Structure[]
     */
    public function getEntiteAffectation()
    {
        throw new \Exception('Méthode pas finie');
        $structure = $this->getService()->getServiceManager()->get('ldapServiceStructure');
        $codes = $this->getNode()->getAttribute('supannEntiteAffectation');
        var_dump($codes);
        return $structure->getBy( $dn, 'dn' );
    }

    /**
     * Retourne la structure d'affectation de la personne
     * @todo à terminer
     * @return Structure
     */
    public function getEntiteAffectationPrincipale()
    {
        throw new \Exception('Méthode pas finie');
        $structure = $this->getService()->getServiceManager()->get('ldapServiceStructure');

        $codes = array();
        $affectations = $this->getNode()->getAttribute('supannAffectation');


        list($code, $description) = explode( ';', $this->supannAffectation );
        $code = $this->supannAffectation;
        if (empty($dn)) return null;
        return $structure->getBy( $dn, 'dn' );
    }

    /**
     * Retourne la structure d'affectation de la personne
     * @todo à terminer
     * @return Structure
     */
    public function getAffectationDescription()
    {
        throw new \Exception('Méthode pas finie');
        $structure = $this->getService()->getServiceManager()->get('ldapServiceStructure');
        
        list($code, $description) = explode( ';', $this->supannAffectation );
        $code = $this->supannAffectation;
        if (empty($dn)) return null;
        return $structure->getBy( $dn, 'dn' );
    }

   /**
     * Retourne true si l'argument est au format "supannRoleEntite".
     *
     * @param string $role
     * @return bool
     */
    static public function isSupannRoleEntite($string, &$role = null, &$typeStructure = null, &$codeStructure = null, &$libelleRole = null)
    {
        if (preg_match($this->role_pattern, $string, $matches)) {
            $role          = $matches[1];
            $typeStructure = $matches[2];
            $codeStructure = $matches[3];
            $libelleRole   = $matches[4];
            return true;
        }
        return false;
    }
}