<?php

$settings = [
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
    'enable_registration'        => false,

    // nom du gestionnaire d'entités à utiliser
    'entity_manager_name'          => 'doctrine.entitymanager.orm_default',

    /**
     * Classes représentant les entités rôle et privilège.
     * - Entité rôle      : héritant de \UnicaenAuth\Entity\Db\AbstractRole      ou implémentant \UnicaenAuth\Entity\Db\RoleInterface.
     * - Entité privilège : héritant de \UnicaenAuth\Entity\Db\AbstractPrivilege ou implémentant \UnicaenAuth\Entity\Db\PrivilegeInterface.
     *
     * Valeurs par défaut :
     * - 'role_entity_class'      : 'UnicaenAuth\Entity\Db\Role'
     * - 'privilege_entity_class' : 'UnicaenAuth\Entity\Db\Privilege'
     */
    'role_entity_class'            => \Oscar\Entity\Role::class,

    /**
     * Attribut LDAP utilisé pour le username des utilisateurs
     */
    'ldap_username'                => "username",

    /**
     * Gestion des autorisations d'usurpation
     */
    'usurpation_allowed_usernames' => [],

    /**
     * Configuration de l'authentification locale.
     */
    'local'                        => [
        'order'   => 2,

        /**
         * Possibilité ou non de s'authentifier à l'aide d'un compte local.
         * Toujours OK si pas de CAS
         */
        'enabled' => true,

        'description' => "Authentification",

        /**
         * Mode d'authentification à l'aide d'un compte dans la BDD de l'application.
         */
        'db'          => [
            'enabled' => true,
        ],

        'ldap' => [
            /**
             * Possibilité ou non de s'authentifier via l'annuaire LDAP ET en local!!.
             */
            'enabled' => true,
        ],
    ],

    'cas'  => [
        /**
         * Ordre d'affichage du formulaire de connexion.
         */
        'order'       => 1,

        /**
         * Activation ou non de ce mode d'authentification.
         */
        'enabled'     => false,

        /**
         * Permet de sauter le formulaire d'authentification "/auth/connexion" si CAS est la seule source d'authentification
         */
        'form_skip' => true,

        /**
         * Description facultative de ce mode d'authentification qui apparaîtra sur la page de connexion.
         */
        'description' => "Cliquez sur le bouton ci-dessous pour vous connecter à l'aide de l'authentification centralisée (CAS).",
    ],

    /**
     * Configuration de l'authentification Shibboleth.
     */
    'shib' => [
        /**
         * Affichage ou non du formulaire d'authentification via l'annuaire LDAP.
         * NB: en réalité cela permet aussi l'authentification avec un compte local.
         */
        'enable'     => false,

        /**
         * Permet de sauter le formulaire d'authentification "/auth/connexion" si CAS est la seule source d'authentification
         */
        'form_skip' => true,

        /**
         * URL de déconnexion.
         */
        'logout_url' => '/Shibboleth.sso/Logout?return=', // NB: '?return=' semble obligatoire!
    ],
];

/***
    $settings['cas']['connection']['default']['params'] = [
        'hostname' => AppConfig::get('cas', 'host'),
        'port'     => AppConfig::get('cas', 'port'),
        'version'  => AppConfig::get('cas', 'version'),
        'uri'      => AppConfig::get('cas', 'uri'),
        'debug'    => AppConfig::get('cas', 'debug'),
    ];
}
/****/

return [
    'unicaen-auth' => $settings,

    'zfcuser' => [
        $k = 'enable_registration' => isset($settings[$k]) ? $settings[$k] : false,
    ],
];