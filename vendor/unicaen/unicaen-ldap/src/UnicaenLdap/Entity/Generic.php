<?php
namespace UnicaenLdap\Entity;

/**
 * Classe mère des adresses génériques de l'annuaire LDAP.
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 */
class Generic extends Entity
{

    protected $type = 'Generic';
    
    /**
     * Liste des classes d'objet nécessaires à la création d'une adresse générique
     * 
     * @var string[] 
     */
    protected $objectClass = array(
	'top',
	'inetOrgPerson',
	'organizationalPerson',
	'person',
	'supannPerson',
	'ucbnEmp'
    );

    /**
     * Liste des attributs contenant des dates
     *
     * @var string[]
     */
    protected $dateTimeAttributes = array(
    );
}