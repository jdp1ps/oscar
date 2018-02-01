<?php
/**
 * Configuration locale du module UnicaenAuth.
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
    'cas' => array(
          // CONFIGURATION DU CAS
//        'connection' => array(
//            'default' => array(
//                'params' => array(
//                    'hostname' => 'cas.unicaen.fr',
//                    'port' => 443,
//                    'version' => "2.0",
//                    'uri' => "",
//                    'debug' => false,
//                ),
//            ),
//        ),
    ),
    /**
     * Identifiants de connexion LDAP autorisés à faire de l'usurpation d'identité.
     * NB: à réserver exclusivement aux tests.
     */
    'usurpation_allowed_usernames' => array('sbouvry', 'bouvry', 'rieup01'),
);

/**
 * You do not need to edit below this line
 */
return array(
    'unicaen-auth' => $settings,
);
