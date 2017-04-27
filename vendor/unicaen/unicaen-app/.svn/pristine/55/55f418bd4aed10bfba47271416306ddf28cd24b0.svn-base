<?php
namespace UnicaenAppTest\Entity\Ldap\TestAsset;

/**
 * Données de test pour créer des entités de type individu LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class People
{
    /**
     * Données de test pour créer des entités.
     * @var array
     */
    static public $data1 = array(
        'dn'                                => "uid=p00000390,ou=people,dc=unicaen,dc=fr",
        'cn'                                => "Paul Hochon",
        'sn'                                => "Hochon",
        'uid'                               => "p00000390",
        'uidnumber'                         => "390",
        'datedenaissance'                   => "19640715",
        'displayname'                       => "Paul Hochon",
        'edupersonorgunitdn'                => array("supannCodeEntite=HS_U09,ou=structures,dc=unicaen,dc=fr", "supannCodeEntite=HS_U094,ou=structures,dc=unicaen,dc=fr"),
        'edupersonprimaryorgunitdn'         => "supannCodeEntite=HS_U09,ou=structures,dc=unicaen,dc=fr",
        'givenname'                         => "Paul",
        'mail'                              => "paul.hochon@unicaen.fr",
        'postaladdress'                     => "CAMPUS 1 - Bâtiment A Etage 3 - Porte AC 328\$ESP DE LA PAIX\$CS 14032$14032\$CAEN CEDEX 5\$FRANCE",
        'sexe'                              => "M",
        'supannactivite'                    => array("{REFERENS}E1A21", "{SILLAND}ADMG", "{SILLAND}GESP", "{SILLAND}INFO"),
        'supannaliaslogin'                  => "hochon",
        'supanncivilite'                    => "M.",
        'supannempid'                       => "390",
        'supannentiteaffectation'           => array("HS_C68", "HS_C681"),
        'supannentiteaffectationprincipale' => "HS_C68",
        'supannetablissement'               => "{UAI}0141408E",
        'supannroleentite'                  => array("[role={SUPANN}D30][type={SUPANN}S231][code=HS_C68][libelle=Directeur]", "[role={SUPANN}D30][type={SUPANN}S302][code=HS_C681][libelle=Directeur]"),
        'supannrolegenerique'               => "{SUPANN}D30",
        'telephonenumber'                   => "+33 2 31 56 62 53",
        'ucbnfonctionstructurelle'          => array("1136;Directeur - Centre de Ressources Informatiques et du Système d'Information (CRISI)", "1139;Directeur technique - Département Système d'Information (DSI)"),
        'ucbnsitelocalisation'              => "11;CAMPUS 1",
        'ucbnsousstructure'                 => "C681;Département Système d'Information (DSI)",
        'ucbnstatus'                        => "TITULAIRE",
        'ucbnstructurerecherche'            => "U255;Écophysiologie Végétale, Agronomie et Nutritions N.C.S. (EVA) [UMR_A 950]",
        'memberof'                          => array("cn=admin_reseau,ou=groups,dc=unicaen,dc=fr", "cn=dept_info,ou=groups,dc=unicaen,dc=fr", "cn=consult_pandemie,ou=groups,dc=unicaen,dc=fr"),
    );
    
    /**
     * Données de test pour créer des entités.
     * Différences avec $data1 :
     *  - Deux noms de familles ('sn')
     *  - Une seule affectation ('supannaffectation')
     *  - Aucun rôle ('supannroleentite')
     * @var array
     */
    static public $data2 = array(
        'dn'                                => "uid=p00000390,ou=people,dc=unicaen,dc=fr",
        'cn'                                => "Paule Hochon",
        'sn'                                => array("Hochon", "Amploix"),
        'uid'                               => "e00000390",
        'uidnumber'                         => "390",
        'datedenaissance'                   => "19640715",
        'displayname'                       => "Paule Hochon",
        'edupersonorgunitdn'                => "supannCodeEntite=HS_U09,ou=structures,dc=unicaen,dc=fr",
        'edupersonprimaryorgunitdn'         => "supannCodeEntite=HS_U09,ou=structures,dc=unicaen,dc=fr",
        'givenname'                         => "Paule",
        'mail'                              => "paule.hochon@unicaen.fr",
        'postaladdress'                     => "CAMPUS 1 - Bâtiment A Etage 3 - Porte AC 328\$ESP DE LA PAIX\$CS 14032$14032\$CAEN CEDEX 5\$FRANCE",
        'sexe'                              => "F",
        'supannactivite'                    => array("{REFERENS}E1A21", "{SILLAND}ADMG", "{SILLAND}GESP", "{SILLAND}INFO"),
        'supannaffectation'                 => "C68;Centre de Ressources Informatiques (CRISI)",
        'supannaliaslogin'                  => "hochon",
        'supanncivilite'                    => "Mme.",
        'supannempid'                       => "390",
        'supannentiteaffectation'           => array("HS_C68", "HS_C681"),
        'supannentiteaffectationprincipale' => "HS_C68",
        'supannetablissement'               => "{UAI}0141408E",
        'supannroleentite'                  => null,
        'supannrolegenerique'               => "{SUPANN}D30",
        'telephonenumber'                   => "+33 2 31 56 62 53",
        'ucbnfonctionstructurelle'          => array("1136;Directeur - Centre de Ressources Informatiques et du Système d'Information (CRISI)", "1139;Directeur technique - Département Système d'Information (DSI)"),
        'ucbnsitelocalisation'              => "11;CAMPUS 1",
        'ucbnsousstructure'                 => "C681;Département Système d'Information (DSI)",
        'ucbnstatus'                        => "TITULAIRE",
        'ucbnstructurerecherche'            => "U255;Écophysiologie Végétale, Agronomie et Nutritions N.C.S. (EVA) [UMR_A 950]",
        'memberof'                          => "cn=consult_pandemie,ou=groups,dc=unicaen,dc=fr",
    );
    
    /**
     * Données de test pour créer des entités dans la branche 'deactivated'
     * @var array
     */
    static public $dataDeactivated = array(
        'dn'               => 'uid=p00003367,ou=deactivated,dc=unicaen,dc=fr',
        'uid'              => 'p00003367',
        'cn'               => 'Hochon Paule',
        'supannaliaslogin' => 'hochon',
    );
}