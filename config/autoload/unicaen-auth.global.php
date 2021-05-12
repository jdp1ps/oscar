<?php
/**
 * UnicaenAuth Global Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */
$settings = array(
    /**
     * Paramètres de connexion au serveur CAS :
     * - pour désactiver l'authentification CAS, le tableau 'cas' doit être vide.
     * - pour l'activer, renseigner les paramètres.
     */
    /**
     * Authentification LDAP (compte établissement).
     */
    /**
     * Authentification BDD (compte dédié à l'appli).
     */
    'local' => [
        'order' => 2,
        'enabled' => true,
        'description' => "Utilisez ce formulaire si vous possédez un compte LDAP établissement ou un compte local dédié à l'application.",

        /**
         * Mode d'authentification à l'aide d'un compte dans la BDD de l'application.
         */
        'db' => [
            'enabled' => true, // doit être activé pour que l'usurpation fonctionne (cf. Authentication/Storage/Db::read()) :-/
        ],

        /**
         * Mode d'authentification à l'aide d'un compte LDAP.
         */
        'ldap' => [
            'enabled' => true,
        ],
    ],


    /**
     * Authentification via la fédération d'identité (Shibboleth).
     */
    'shib' => [
        'order' => 4,
        'enabled' => false,
        'description' =>
            "Cliquez sur le bouton ci-dessous pour accéder à l'authentification via la fédération d'identité. " .
            "<strong>NB: Vous devrez utiliser votre compte " .
            "&laquo; <a href='http://vie-etudiante.unicaen.fr/vie-numerique/etupass/'>etupass</a> &raquo; " .
            "pour vous authentifier...</strong>",

        /**
         * URL de déconnexion.
         */
        'logout_url' => '/Shibboleth.sso/Logout?return=', // NB: '?return=' semble obligatoire!

        /**
         * Simulation d'authentification d'un utilisateur.
         */
        //'simulate' => [
        //    'eppn'        => 'eppn@domain.fr',
        //    'supannEmpId' => '00012345',
        //],

        /**
         * Alias éventuels des clés renseignées par Shibboleth dans la variable superglobale $_SERVER
         * une fois l'authentification réussie.
         */
        'aliases' => [
            'eppn'                   => 'HTTP_EPPN',
            'mail'                   => 'HTTP_MAIL',
            'eduPersonPrincipalName' => 'HTTP_EPPN',
            'supannEtuId'            => 'HTTP_SUPANNETUID',
            'supannEmpId'            => 'HTTP_SUPANNEMPID',
            'supannCivilite'         => 'HTTP_SUPANNCIVILITE',
            'displayName'            => 'HTTP_DISPLAYNAME',
            'sn'                     => 'HTTP_SN',
            'givenName'              => 'HTTP_GIVENNAME',
        ],

        /**
         * Clés dont la présence sera requise par l'application dans la variable superglobale $_SERVER
         * une fois l'authentification réussie.
         */
        //'required_attributes' => [
        //    'eppn',
        //    'mail',
        //    'eduPersonPrincipalName',
        //    'supannCivilite',
        //    'displayName',
        //    'sn|surname', // i.e. 'sn' ou 'surname'
        //    'givenName',
        //    'supannEtuId|supannEmpId',
        //],
    ],

    'cas' => [
        /**
         * Ordre d'affichage du formulaire de connexion.
         */
        'order' => 1,

        /**
         * Activation ou non de ce mode d'authentification.
         */
        'enabled' => false,

        /**
         * Description facultative de ce mode d'authentification qui apparaîtra sur la page de connexion.
         */
        // 'description' => "Cliquez sur le bouton ci-dessous pour accéder à l'authentification centralisée.",

        /**
         * Infos de connexion au serveur CAS.
         */
        'connection' => [
            'default' => [
                'params' => [
                    'hostname' => 'host.domain.fr',
                    'port'     => 443,
                    'version'  => "2.0",
                    'uri'      => "",
                    'debug'    => false,
                ],
            ],
        ]
    ],

    /**
     * Identifiants de connexion LDAP autorisés à faire de l'usurpation d'identité.
     * NB: à réserver exclusivement aux tests.
     */
    'usurpation_allowed_usernames' => array('bouvry', 'turbout'),
    // Champ utilisé pour l'autentification (côté LDAP)
    //'ldap_username' => 'supanaliaslogin',
    /**
     * Flag indiquant si l'utilisateur authenitifié avec succès via l'annuaire LDAP doit
     * être enregistré/mis à jour dans la table des utilisateurs de l'appli.
     */
    'save_ldap_user_in_database' => true,
    /**
     * Enable registration
     * Allows users to register through the website.
     * Accepted values: boolean true or false
     */
    'enable_registration' => false,
    'enable_privileges' => true,
);

$config = [
    'unicaen-auth' => $settings,
    'zfcuser' => [
        $k = 'enable_registration' => isset($settings[$k]) ? $settings[$k] : false,
        'user_entity_class' => 'Oscar\Entity\Authentification',
    ],
    'bjyauthorize' => [
        'identity_provider' => 'UnicaenAuth\Provider\Identity\Chain',

    ]
];

if ($settings['enable_privileges']) {
    $privileges = [
        'bjyauthorize' => [
            'guards' => [
                'UnicaenAuth\Guard\PrivilegeController' => [
                    [
                        'controller' => 'UnicaenAuth\Controller\Droits',
                        'action' => ['index'],
                        'privileges' => [
                            \UnicaenAuth\Provider\Privilege\Privileges::DROIT_ROLE_VISUALISATION,
                            \UnicaenAuth\Provider\Privilege\Privileges::DROIT_PRIVILEGE_VISUALISATION,
                        ],
                    ],
                    [
                        'controller' => 'UnicaenAuth\Controller\Droits',
                        'action' => ['roles'],
                        'privileges' => [\UnicaenAuth\Provider\Privilege\Privileges::DROIT_ROLE_VISUALISATION],
                    ],
                    [
                        'controller' => 'UnicaenAuth\Controller\Droits',
                        'action' => ['privileges'],
                        'privileges' => [\UnicaenAuth\Provider\Privilege\Privileges::DROIT_PRIVILEGE_VISUALISATION],
                    ],
                    [
                        'controller' => 'UnicaenAuth\Controller\Droits',
                        'action' => ['role-edition', 'role-suppression'],
                        'privileges' => [\UnicaenAuth\Provider\Privilege\Privileges::DROIT_ROLE_EDITION],
                    ],
                    [
                        'controller' => 'UnicaenAuth\Controller\Droits',
                        'action' => ['privileges-modifier'],
                        'privileges' => [\UnicaenAuth\Provider\Privilege\Privileges::DROIT_PRIVILEGE_EDITION],
                    ],
                ],
            ],
        ],
        'navigation' => [
            // The DefaultNavigationFactory we configured uses 'default' as the sitemap key
            'default' => [
                // And finally, here is where we define our page hierarchy
                'home' => [
                    'pages' => [
                        'droits' => [
                            'label' => 'Droits d\'accès',
                            'title' => 'Gestion des droits d\'accès',
                            'route' => 'droits',
                            'resource' => \UnicaenAuth\Guard\PrivilegeController::getResourceId('UnicaenAuth\Controller\Droits',
                                'index'),
                            'pages' => [
                                'roles' => [
                                    'label' => "Rôles",
                                    'title' => "Gestion des rôles",
                                    'route' => 'droits/roles',
                                    'resource' => \UnicaenAuth\Guard\PrivilegeController::getResourceId('UnicaenAuth\Controller\Droits',
                                        'roles'),
                                    'withtarget' => true,
                                ],
                                'privileges' => [
                                    'label' => "Privilèges",
                                    'title' => "Gestion des privilèges",
                                    'route' => 'droits/privileges',
                                    'resource' => \UnicaenAuth\Guard\PrivilegeController::getResourceId('UnicaenAuth\Controller\Droits',
                                        'privileges'),
                                    'withtarget' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
} else {
    $privileges = [];
}

return array_merge_recursive($config, $privileges);

/**
 * You do not need to edit below this line
 *
 * return array(
 * 'unicaen-auth' => $settings,
 * 'zfcuser'      => array(
 * $k='enable_registration' => isset($settings[$k]) ? $settings[$k] : false,
 * 'user_entity_class' => 'Oscar\Entity\Authentification',
 * 'enable_user_state' => true // Permet de gérer l'état du compte
 * ),
 *
 * );*/