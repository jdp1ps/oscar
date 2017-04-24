<?php
namespace UnicaenAppTest\Mapper\Ldap\TestAsset;

/**
 * Données de test représentant des résultats de recherche d'individu LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class Structure
{
    /**
     * Structure de niveau 1, donc sans structure mère ('supanncodeentiteparent' absent)
     * @var array
     */
    static public $data1 = array(
        'dn'               => array("supannCodeEntite=HS_UNIV,ou=structures,dc=unicaen,dc=fr"),
        'ou'               => array("UNIVERSITE DE CAEN"),
        'supanncodeentite' => array("HS_UNIV"),
        'description'      => array("Université de Caen Normandie"),
        'postaladdress'    => array("CAMPUS 1 - Bâtiment P Etage 3 - Porte PR 308\$ESP DE LA PAIX\$CS 14032$14032\$CAEN CEDEX 5\$FRANCE"),
        'supanntypeentite' => array("{SUPANN}S102", "{SUPANN}S231"),
        'telephonenumber'  => array("+33 2 31 56 62 54"),
    );
    
    /**
     * DIfférence avec $data1 :
     *  - Structure de niveau 2 ('supanncodeentiteparent' présent)
     *  - 'facsimiletelephonenumber' présent
     * @var array
     */
    static public $data2 = array(
        'dn'                       => array("supannCodeEntite=HS_C68,ou=structures,dc=unicaen,dc=fr"),
        'ou'                       => array("DSI"),
        'supanncodeentite'         => array("HS_C68"),
        'description'              => array("Direction du Système d'Information (DSI)"),
        'facsimiletelephonenumber' => array("+33 2 31 56 63 80"),
        'postaladdress'            => array("CAMPUS 2 - Résidence Universitaire Rez de Chaussée - Crèche Campus 2\$BVD Maréchal Juin$$14032\$CAEN\$FRANCE"),
        'supanncodeentiteparent'   => array("HS_UNIV"),
        'supanntypeentite'         => array("{SUPANN}S231"),
        'telephonenumber'          => array("+33 2 31 56 62 54"),
    );
    
    /**
     * Différences avec $data1 :
     *  - Structure de niveau 4
     * @var array
     */
    static public $data3 = array(
        'dn'                     => array("supannCodeEntite=HS_G72J2,ou=structures,dc=unicaen,dc=fr"),
        'ou'                     => array("Recrutement BIATSS"),
        'supanncodeentite'       => array("HS_G72J2"),
        'description'            => array("Recrutement BIATSS"),
        'postaladdress'          => array("CAMPUS 1 - Bâtiment P Etage 4 -\$ESP DE LA PAIX\$CS 14032$14032\$CAEN CEDEX 5\$FRANCE"),
        'supanncodeentiteparent' => array("HS_G72J"),
        'supanntypeentite'       => array("{SUPANN}S302", "{SUPANN}S231"),
        'telephonenumber'        => array("+33 2 31 56 62 54"),
    );
}