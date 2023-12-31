<?php

// Par défaut, Oscar n'utilise pas le serveur Websocker
$socket = false;

// Si le fichier de configuration est présent, on charge la configuration
if( file_exists($pathSocketConfig = __DIR__.'/../../socket/config.json') ){
    $socketConf = json_decode(file_get_contents($pathSocketConfig), true);
    $socket = $socketConf['socket'];
}

// Rétro compatibilité avec le module Doctrine
if(  !defined('APP_DIR') ){
    define('APP_DIR', realpath(__DIR__.'/../../'));
}

return array(
    'view_manager' => array(
        'display_not_found_reason' => getenv('APPLICATION_ENV') == 'development',
        'display_exceptions'       => getenv('APPLICATION_ENV') == 'development',
    ),

    // Oscar
    'oscar' => [
        // Oscar Live
        'socket' => $socket,

        ////////////////////////////////////////////////////////////////////////
        //
        // PATHS
        //
        // Emplacements utilisés par oscar pour stoquer des fichiers.
        // Ces emplacements doivent être accessibles et ouvert en écriture.
        //
        ////////////////////////////////////////////////////////////////////////
        'paths' => [
            // Emplacement où sont stoqués les documents Oscar
            'document_oscar' => '/opt/documents/activity/',

            // Emplacement où sont stoqués les documents administratifs Oscar
            'document_admin_oscar' => '/opt/documents/admin/',

            // Emplacement de l'index de recherche (emplacement où Lucene écrit
            // sont index de recherche - un DOSSIER).
            'search_activity' => '/opt/documents/luceneindex/',
        ],
        ////////////////////////////////////////////////////////////////////////
        //
        // CONNECTORS
        //
        // Les connecteurs sont les points d'entrées pour les données utilisées
        // dans Oscar.
        // Pour le moment ne sont gérés que :
        // - Person (participants aux activités de recherche)
        // - Organization (Structures/Organisations impliquées dans les activités)
        //
        ////////////////////////////////////////////////////////////////////////
        'connectors' => [
            // Connection au tiers d'où seront obtenu les structures
            'organization' => [
               'rest' => [
                  'class'     => \Oscar\Connector\ConnectorOrganizationREST::class,
                  'params'    => APP_DIR . '/config/connectors/organization_rest.yml',
                  'editable'  => false
               ]
            ],
            'person' => [
               'rest' => [
                  'class'     => \Oscar\Connector\ConnectorPersonREST::class,
                  'params'    => APP_DIR . '/config/connectors/person_rest.yml',
                  'editable'  => false
               ]
            ]
        ],

        // Système d'envoi des mails
        'mailer' => [
            // Assure la distribution
            'transport' => [
                'host' => 'smtp.host.tld',
                'port' => 465,
                'username' => 'jean-claude.dus@unicaen.fr',
                'password' => 'XXXXXXXXXX',
                'security' => 'ssl',
            ],
            // Use for FROM email, same as transport maybe
            'from' => [ 'oscar-bot@unicaen.fr' => 'Oscar Bot'],

            // Envoi d'une copy
            'copy' => ['stephane.bouvry@unicaen.fr'],

            // Envoi activé
            'send' => false,

            // Préfixe ajouté dans les sujets
            'subjectPrefix' => '[OSCAR DEV]'
        ]
    ],

    // Accès BDD
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                //////////////////////////////////////////////////////// DRIVERS
                // MySQL
                //'driverClass' => '\Doctrine\DBAL\Driver\PDOMySql\Driver',
                // Postgresql
                'driverClass' => '\Doctrine\DBAL\Driver\PDOPgSql\Driver',
                ////////////////////////////////////////////////////////////////

                'doctrine_type_mappings' => array(
                    'enum' => 'string'
                ),

                ////////////////////////////////////////////////////// CONNEXION
                'params' => array(
                    'host'     => 'localhost',
                    'port'     => '5432',
                    'user'     => 'oscar',
                    'password' => 'oscar',
                    'dbname'   => 'oscar',
                    'charset'  => 'utf8'
                ),
            ),
        ),
    ),
);
