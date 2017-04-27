<?php
namespace UnicaenLdap\Service;

/**
 * Classe regroupant les opérations de recherche des comptes génériques dans l'annuaire LDAP.
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 */
class Generic extends Service
{

    protected $type = 'Generic';

    protected $ou = array('generic');
}