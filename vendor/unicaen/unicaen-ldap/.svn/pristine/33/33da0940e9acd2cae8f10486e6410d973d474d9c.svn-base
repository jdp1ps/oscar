<?php
namespace UnicaenLdap\Entity;

/**
 * Classe mère des utilisateurs système de l'annuaire LDAP.
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 */
class System extends Entity
{

    protected $type = 'System';
    
    /**
     * Liste des classes d'objet nécessaires à la création d'un compte système
     * 
     * @var string[] 
     */
    protected $objectClass = array(
	'top',
	'inetOrgPerson',
	'organizationalPerson',
	'person',
    );

    /**
     * Liste des attributs contenant des dates
     *
     * @var string[]
     */
    protected $dateTimeAttributes = array(
    );
}