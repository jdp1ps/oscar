<?php
namespace UnicaenLdap\Entity;

use DateTime;

/**
 * Classe mère des groupes de l'annuaire LDAP.
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 */
class Group extends Entity
{

    protected $type = 'Group';

    /**
     * Liste des classes d'objet nécessaires à la création d'un groupe
     * 
     * @var string[] 
     */
    protected $objectClass = array(
	'groupOfNames',
	'supannGroupe'
    );
	    
    /**
     * Liste des attributs contenant des dates
     *
     * @var string[]
     */
    protected $dateTimeAttributes = array(
        'supannGroupeDateFin',
    );


    


    /**
     * Détermine si un groupe est valide ou non, c'est-à-dire si sa date de fin de validité n'est pas antérieure à la date testée
     *
     * @param DateTime $dateObservation
     */
    public function isValid( DateTime $dateObservation = null )
    {
        if (empty($dateObservation)) $dateObservation = new DateTime;
        $dateControle = $this->supannGroupeDateFin;
        return empty($dateControle) || ($dateControle >= $dateObservation->getTimestamp());
    }

    /**
     * Détermine si un groupe est obsolète ou non, c'est-à-dire si sa date de fin de validité est antérieure à la date testée
     *
     * @param DateTime $dateObservation
     */
    public function isObsolete( DateTime $dateObservation = null )
    {
        return ! $this->isValid();
    }

    /**
     * Retourne la liste des personnes membres du groupe
     *
     * @param string $orderBy Champ de tri (au besoin)
     * @return People[]
     */
    public function getPeople( $orderBy=null )
    {
        /* @var $people \UnicaenLdap\Service\People */
        $people = $this->getService()->getServiceManager()->get('ldapServicePeople');
        return $people->getAllBy( $this->get('member'), 'dn', $orderBy );
    }
}