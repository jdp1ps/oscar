<?php
/**
 * UnicaenApp Global Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, 
 * drop this config file in it and change the values as you wish.
 */
$settings = array(
    /**
     * Informations concernant cette application
     */
    'ldap' => [
        'dn' => [
            'UTILISATEURS_BASE_DN'                  => 'ou=people,dc=unicaen,dc=fr',
            'UTILISATEURS_DESACTIVES_BASE_DN'       => 'ou=deactivated,dc=unicaen,dc=fr',
            'GROUPS_BASE_DN'                        => 'ou=groups,dc=unicaen,dc=fr',
            'STRUCTURES_BASE_DN'                    => 'ou=structures,dc=unicaen,dc=fr',
        ],
        'filters' => [
            'LOGIN_FILTER'                          => '(supannAliasLogin=%s)',
            'UTILISATEUR_STD_FILTER'                => '(|(uid=p*)(&(uid=e*)(eduPersonAffiliation=student)))',
            'CN_FILTER'                             => '(cn=%s)',
            'NAME_FILTER'                           => '(cn=%s*)',
            'UID_FILTER'                            => '(uid=%s)',
            'NO_INDIVIDU_FILTER'                    => '(supannEmpId=%08s)',
            'AFFECTATION_FILTER'                    => '(&(uid=*)(eduPersonOrgUnitDN=%s))',
            'AFFECTATION_CSTRUCT_FILTER'            => '(&(uid=*)(|(ucbnSousStructure=%s;*)(supannAffectation=%s;*)))',
            'LOGIN_OR_NAME_FILTER'                  => '(|(supannAliasLogin=%s)(cn=%s*))',
            'MEMBERSHIP_FILTER'                     => '(memberOf=%s)',
            'AFFECTATION_ORG_UNIT_FILTER'           => '(eduPersonOrgUnitDN=%s)',
            'AFFECTATION_ORG_UNIT_PRIMARY_FILTER'   => '(eduPersonPrimaryOrgUnitDN=%s)',
            'ROLE_FILTER'                           => '(supannRoleEntite=[role={SUPANN}%s][type={SUPANN}%s][code=%s]*)',
            'PROF_STRUCTURE'                        => '(&(eduPersonAffiliation=teacher)(eduPersonOrgUnitDN=%s))',
            'FILTER_STRUCTURE_DN'		            => '(%s)',
            'FILTER_STRUCTURE_CODE_ENTITE'	        => '(supannCodeEntite=%s)',
            'FILTER_STRUCTURE_CODE_ENTITE_PARENT'   => '(supannCodeEntiteParent=%s)',
        ],

        'log_path' => '/tmp/oscar-ldap.log'
    ],
    'app_infos' => array(
        'nom'     => 'O.S.C.A.R',
        'desc'    => 'Organisation et Suivi des Contrats et des Activités de Recherche',
        'version' => 'beta',
        'date'    => '2016',
        'contact' => array('mail' => 'dsi.applications@unicaen.fr', /*'tel' => '01 02 03 04 05'*/),
        'mentionsLegales'        => 'http://www.unicaen.fr/outils-portail-institutionnel/mentions-legales/',
        'informatiqueEtLibertes' => 'http://www.unicaen.fr/outils-portail-institutionnel/informatique-et-libertes/',
    ),

   // 'session_refresh_period' => 0, // 0 <=> aucune requête exécutée


);

/**
 * You do not need to edit below this line
 */
return array(
    'unicaen-app' => $settings,
    //
    // Session configuration.
    //
    'session_config' => [
        // Session cookie will expire in 1 hour.
        'cookie_lifetime' => 60*60*1,
        // Session data will be stored on server maximum for 30 days.
        'gc_maxlifetime'     => 60*60*24*30,
    ],
    //
    // Session manager configuration.
    //
    'session_manager' => [
        // Session validators (used for security).
        'validators' => [
            \Zend\Session\Validator\RemoteAddr::class,
            \Zend\Session\Validator\HttpUserAgent::class,

            // Erreur rencontrée avec ce validateur lorsqu'on passe en "Version pour ordinateur" sur un téléphone Android :
            // `Fatal error: Uncaught Zend\Session\Exception\RuntimeException: Session validation failed
            //  in /var/www/app/vendor/zendframework/zend-session/src/SessionManager.php on line 162`
            //HttpUserAgent::class,
        ]
    ],
    //
    // Session storage configuration.
    //
    'session_storage' => [
        'type' => \Zend\Session\Storage\SessionArrayStorage::class
    ],

);