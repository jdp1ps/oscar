<?php
namespace UnicaenAppTest\Entity\Ldap\TestAsset;

/**
 * Données de test pour créer des entités de type structure LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class Structure
{
    /**
     * Données de test pour créer des entités.
     * Structure de niveau 1, donc sans structure mère ('supanncodeentiteparent' absent)
     * @var array
     */
    static public $data1 = array(
        'dn'               => "supannCodeEntite=HS_UNIV,ou=structures,dc=unicaen,dc=fr",
        'ou'               => "UNIVERSITE DE CAEN",
        'supanncodeentite' => "HS_UNIV",
        'description'      => "Université de Caen Normandie",
        'postaladdress'    => "CAMPUS 1 - Bâtiment P Etage 3 - Porte PR 308\$ESP DE LA PAIX\$CS 14032$14032\$CAEN CEDEX 5\$FRANCE",
        'supanntypeentite' => array("{SUPANN}S102", "{SUPANN}S231"),
        'telephonenumber'  => "+33 2 31 56 62 54",
    );
    
    /**
     * Données de test pour créer des entités.
     * DIfférence avec $data1 :
     *  - Structure de niveau 2 ('supanncodeentiteparent' présent)
     *  - 'facsimiletelephonenumber' présent
     * @var array
     */
    static public $data2 = array(
        'dn'                       => "supannCodeEntite=HS_C68,ou=structures,dc=unicaen,dc=fr",
        'ou'                       => "DSI",
        'supanncodeentite'         => "HS_C68",
        'description'              => "Direction du Système d'Information (DSI)",
        'facsimiletelephonenumber' => "+33 2 31 56 63 80",
        'postaladdress'            => "CAMPUS 2 - Résidence Universitaire Rez de Chaussée - Crèche Campus 2\$BVD Maréchal Juin$$14032\$CAEN\$FRANCE",
        'supanncodeentiteparent'   => "HS_UNIV",
        'supanntypeentite'         => "{SUPANN}S231",
        'telephonenumber'          => "+33 2 31 56 62 54",
    );
    
    /**
     * Données de test pour créer des entités.
     * Différences avec $data1 :
     *  - Structure de niveau 4
     * @var array
     */
    static public $data3 = array(
        'dn'                     => "supannCodeEntite=HS_G72J2,ou=structures,dc=unicaen,dc=fr",
        'ou'                     => "Recrutement BIATSS",
        'supanncodeentite'       => "HS_G72J2",
        'description'            => "Recrutement BIATSS",
        'postaladdress'          => "CAMPUS 1 - Bâtiment P Etage 4 -\$ESP DE LA PAIX\$CS 14032$14032\$CAEN CEDEX 5\$FRANCE",
        'supanncodeentiteparent' => "HS_G72J",
        'supanntypeentite'       => array("{SUPANN}S302", "{SUPANN}S231"),
        'telephonenumber'        => "+33 2 31 56 62 54",
    );
}