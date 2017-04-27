<?php
namespace UnicaenAppTest\Entity\Ldap\TestAsset;

/**
 * Données de test pour créer des entités de type groupe LDAP.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class Group
{
    /**
     * Données de test pour créer des entités.
     * @var array
     */
    static public $data1 = array(
        'dn'                  => 'cn=admin_reseau,ou=groups,dc=unicaen,dc=fr',
        'cn'                  => 'admin_reseau',
        'member'              => array('uid=p00000478,ou=people,dc=unicaen,dc=fr', 'uid=p00001888,ou=people,dc=unicaen,dc=fr', 'uid=p00015593,ou=people,dc=unicaen,dc=fr'),
        'description'         => "Administrateurs réseau de la DSI",
        'supanngroupedatefin' => '999912310000Z',
    );
    
    /**
     * Données de test pour créer des entités.
     * @var array
     */
    static public $data2 = array(
        'dn'                  => 'cn=dept_info,ou=groups,dc=unicaen,dc=fr',
        'cn'                  => 'dept_info',
        'member'              => array('uid=p00000478,ou=people,dc=unicaen,dc=fr', 'uid=p00001888,ou=people,dc=unicaen,dc=fr', 'uid=p00015593,ou=people,dc=unicaen,dc=fr'),
        'description'         => "Département Informatique",
        'supanngroupedatefin' => '999912310000Z',
    );
    
    /**
     * Données de test pour créer des entités.
     * Différence avec $data1 :
     *  - 'supannGroupeDateFin' au 14/01/2013
     * @var array
     */
    static public $data3 = array(
        'dn'                  => 'cn=consult_pandemie,ou=groups,dc=unicaen,dc=fr',
        'cn'                  => 'consult_pandemie',
        'member'              => array('uid=p00000478,ou=people,dc=unicaen,dc=fr', 'uid=p00001888,ou=people,dc=unicaen,dc=fr', 'uid=p00015593,ou=people,dc=unicaen,dc=fr'),
        'description'         => "Correspondants Consultation Pandémie",
        'supanngroupedatefin' => '201301140000Z',
    );
    
    /**
     * Données de test pour créer des entités.
     * @var array
     */
    static public $data4 = array(
        'dn'                  => 'cn=rssi,ou=groups,dc=unicaen,dc=fr',
        'cn'                  => 'rssi',
        'member'              => array('uid=p00000478,ou=people,dc=unicaen,dc=fr', 'uid=p00001888,ou=people,dc=unicaen,dc=fr', 'uid=p00015593,ou=people,dc=unicaen,dc=fr'),
        'description'         => "Responsable de la Sécurité du Système d'Information",
        'supanngroupedatefin' => '999912310000Z',
    );
}