<?php
/**
 * UnicaenApp Local Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, 
 * drop this config file in it and change the values as you wish.
 */
$settings = array(
    /**
     * Connexions aux annuaires LDAP.
     * NB: Compte admin requis pour récupération coordonnées, affectations, rôles, etc.
     */
//    'ldap' => array(
//        'connection' => array(
//            'default' => array(
//                'params' => array(
//                    'host'                => 'host.domain.fr',
//                    'username'            => 'uid=xxxxxxxxx,ou=xxxxxxxxx,dc=domain,dc=fr',
//                    'password'            => 'xxxxxxxxxxxx',
//                    'baseDn'              => 'ou=xxxxxxxxxx,dc=domain,dc=fr',
//                    'bindRequiresDn'      => true,
//                    'accountFilterFormat' => "(&(objectClass=posixAccount)(supannAliasLogin=%s))",
//                )
//            )
//        )
//    ),
    /**
     * Connexions aux bases de données via Doctrine (http://www.doctrine-project.org/).
     */
//    'doctrine' => array(
//        'connection' => array(
//            'orm_default' => array(
//                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
//                'params' => array(
//                    'host'     => 'localhost',
//                    'port'     => '3306',
//                    'user'     => 'root',
//                    'password' => 'root',
//                    'dbname'   => 'squelette',
//                )
//            ),
//        ),
//    ),
    /**
     * Options concernant l'envoi de mail par l'application.
     */
    'mail' => array(
        // transport des mails
        'transport_options' => array(
            'host' => 'smtp.unicaen.fr',
            'port' => 25,
        ),
        // adresses à substituer à celles des destinataires originaux ('CURRENT_USER' équivaut à l'utilisateur connecté)
        'redirect_to' => array('dsi.applications@unicaen.fr', /*'CURRENT_USER'*/),
        // désactivation totale de l'envoi de mail par l'application
        'do_not_send' => false,
    ),
);

/**
 * You do not need to edit below this line
 */
return array(
    'unicaen-app' => $settings,
    'doctrine' => isset($settings['doctrine']) ? $settings['doctrine'] : array(),
);