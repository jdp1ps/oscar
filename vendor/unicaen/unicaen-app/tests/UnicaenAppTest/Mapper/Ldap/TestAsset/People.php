<?php
namespace UnicaenAppTest\Mapper\Ldap\TestAsset;

/**
 * Données de test représentant des résultats de recherche d'individu LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class People
{
    /**
     * @var array
     */
    static public $data1 = array(
        'dn'                                => array("uid=p00000390,ou=people,dc=unicaen,dc=fr"),
        'cn'                                => array("Paul Hochon"),
        'sn'                                => array("Hochon"),
        'uid'                               => array("p00000390"),
        'uidnumber'                         => array("390"),
        'datedenaissance'                   => array("19640715"),
        'displayname'                       => array("Paul Hochon"),
        'edupersonorgunitdn'                => array("supannCodeEntite=HS_U09,ou=structures,dc=unicaen,dc=fr", "supannCodeEntite=HS_U094,ou=structures,dc=unicaen,dc=fr"),
        'edupersonprimaryorgunitdn'         => array("supannCodeEntite=HS_U09,ou=structures,dc=unicaen,dc=fr"),
        'givenname'                         => array("Paul"),
        'mail'                              => array("paul.hochon@unicaen.fr"),
        'postaladdress'                     => array("CAMPUS 1 - Bâtiment A Etage 3 - Porte AC 328\$ESP DE LA PAIX\$CS 14032$14032\$CAEN CEDEX 5\$FRANCE"),
        'sexe'                              => array("M"),
        'supannactivite'                    => array("{REFERENS}E1A21", "{SILLAND}ADMG", "{SILLAND}GESP", "{SILLAND}INFO"),
        'supannaliaslogin'                  => array("hochon"),
        'supanncivilite'                    => array("M."),
        'supannempid'                       => array("390"),
        'supannentiteaffectation'           => array("HS_C68", "HS_C681"),
        'supannentiteaffectationprincipale' => array("HS_C68"),
        'supannetablissement'               => array("{UAI}0141408E"),
        'supannroleentite'                  => array("[role={SUPANN}D30][type={SUPANN}S231][code=HS_C68]", "[role={SUPANN}D30][type={SUPANN}S302][code=HS_C681]"),
        'supannrolegenerique'               => array("{SUPANN}D30"),
        'telephonenumber'                   => array("+33 2 31 56 62 53"),
        'ucbnfonctionstructurelle'          => array("1136;Directeur - Centre de Ressources Informatiques et du Système d'Information (CRISI)", "1139;Directeur technique - Département Système d'Information (DSI)"),
        'ucbnsitelocalisation'              => array("11;CAMPUS 1"),
        'ucbnsousstructure'                 => array("C681;Département Système d'Information (DSI)"),
        'ucbnstatus'                        => array("TITULAIRE"),
        'ucbnstructurerecherche'            => array("U255;Écophysiologie Végétale, Agronomie et Nutritions N.C.S. (EVA) [UMR_A 950]"),
        'memberof'                          => array("cn=admin_reseau,ou=groups,dc=unicaen,dc=fr", "cn=dept_info,ou=groups,dc=unicaen,dc=fr", "cn=consult_pandemie,ou=groups,dc=unicaen,dc=fr"),
    );
    
    /**
     * Différences avec $data1 :
     *  - Deux noms de familles ('sn')
     *  - Une seule affectation ('supannaffectation')
     *  - Aucun rôle ('supannroleentite')
     * @var array
     */
    static public $data2 = array(
        'dn'                                => array("uid=p00000450,ou=people,dc=unicaen,dc=fr"),
        'cn'                                => array("Paule Hochon"),
        'sn'                                => array("Hochon", "Amploix"),
        'uid'                               => array("e00000450"),
        'uidnumber'                         => array("450"),
        'datedenaissance'                   => array("19690715"),
        'displayname'                       => array("Paule Hochon"),
        'edupersonorgunitdn'                => array("supannCodeEntite=HS_U09,ou=structures,dc=unicaen,dc=fr"),
        'edupersonprimaryorgunitdn'         => array("supannCodeEntite=HS_U09,ou=structures,dc=unicaen,dc=fr"),
        'givenname'                         => array("Paule"),
        'mail'                              => array("paule.hochon@unicaen.fr"),
        'postaladdress'                     => array("CAMPUS 1 - Bâtiment A Etage 3 - Porte AC 328\$ESP DE LA PAIX\$CS 14032$14032\$CAEN CEDEX 5\$FRANCE"),
        'sexe'                              => array("F"),
        'supannactivite'                    => array("{REFERENS}E1A21", "{SILLAND}ADMG", "{SILLAND}GESP", "{SILLAND}INFO"),
        'supannaffectation'                 => array("C68;Centre de Ressources Informatiques (CRISI)"),
        'supannaliaslogin'                  => array("hochon"),
        'supanncivilite'                    => array("Mme."),
        'supannempid'                       => array("450"),
        'supannentiteaffectation'           => array("HS_C68", "HS_C681"),
        'supannentiteaffectationprincipale' => array("HS_C68"),
        'supannetablissement'               => array("{UAI}0141408E"),
        'supannroleentite'                  => null,
        'supannrolegenerique'               => array("{SUPANN}D30"),
        'telephonenumber'                   => array("+33 2 31 56 62 53"),
        'ucbnfonctionstructurelle'          => array("1136;Directeur - Centre de Ressources Informatiques et du Système d'Information (CRISI)", "1139;Directeur technique - Département Système d'Information (DSI)"),
        'ucbnsitelocalisation'              => array("11;CAMPUS 1"),
        'ucbnsousstructure'                 => array("C681;Département Système d'Information (DSI)"),
        'ucbnstatus'                        => array("TITULAIRE"),
        'ucbnstructurerecherche'            => array("U255;Écophysiologie Végétale, Agronomie et Nutritions N.C.S. (EVA) [UMR_A 950]"),
        'memberof'                          => array("cn=consult_pandemie,ou=groups,dc=unicaen,dc=fr"),
    );
    
    /**
     * @var array
     */
    static public $dataDeactivated = array(
        'dn'               => array('uid=p00003367,ou=deactivated,dc=unicaen,dc=fr'),
        'uid'              => array('p00003367'),
        'cn'               => array('Hochon Paule'),
        'supannaliaslogin' => array('hochon'),
    );
}