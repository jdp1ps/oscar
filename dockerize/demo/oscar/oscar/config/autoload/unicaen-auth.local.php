<?php
/**
 * Configuration locale du module UnicaenAuth.
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */
$settings = array(
    'local' => [
        'order' => 1,
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
     * Paramètres de connexion au serveur CAS :
     * - pour désactiver l'authentification CAS, le tableau 'cas' doit être vide.
     * - pour l'activer, renseigner les paramètres.
     */
    'cas' => array(
        'enabled' => false,
          // CONFIGURATION DU CAS
        'connection' => array(
            'default' => array(
                'params' => array(
                    'hostname' => 'cas.unicaen.fr',
                    'port' => 443,
                    'version' => "2.0",
                    'uri' => "",
                    'debug' => false,
                ),
            ),
        ),
    ),
    // 'usurpation_allowed_usernames' => array('login1', 'login2'),

    // Champ utilisé pour l'autentification (côté LDAP)
    // 'ldap_username' => 'supannaliaslogin',
);

/**
 * You do not need to edit below this line
 */
return array(
    'unicaen-auth' => $settings,
);
