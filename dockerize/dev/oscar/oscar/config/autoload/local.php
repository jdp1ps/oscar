<?php
return array(
    'view_manager' => array(
        'display_not_found_reason' => getenv('APPLICATION_ENV') == 'development',
        'display_exceptions'       => getenv('APPLICATION_ENV') == 'development',
    ),

    // Oscar
    'oscar' => [
        // Oscar Live
        'socket' => false,

        // gearman
        'gearman-job-server-host'=>'oscar_dev_gearman_spartan',

        ////////////////////////////////////////////////////////////////////////
        // ! EXPERIMENTAL !
        'generated-documents' => [],

        // Répartition horaire
        'declarationsDurations' => [
            'dayLength'     => [
                'value' => 7.5,
                'max' => 10.0,
                'min' => 5.0,
                'days' => ['1' => 8.0, '2' => 8.0, '3' => 8.0, '4' => 8.0, '5' => 8.0, '6' => 0.0, '7' => 0.0]
            ],

            'weekLength'     => [
                'value' => 37.0,
                'max' => 44.0,
                'min' => 20.0,
                'userChange' => false
            ],

            'monthLength' => [
                'value' => 144.0,
                'max'   => 184.0,
                'min' => 80.0,
                'userChange' => false
            ],

            'weekExceptions' => [
                '3'         => 3.0,
            ],
        ],

        // Exemples de modèle horaire
        'scheduleModeles' => [],

        // Désactive / Active l'option d'importation des calendriers
        'importEnable' => false,


        ////////////////////////////////////////////////////////////////////////
        //
        // PATHS  (Optionnel)
        //
        // Emplacements utilisés par oscar pour stoquer des fichiers.
        // Ces emplacements doivent être accessibles et ouvert en écriture.
        //
        ////////////////////////////////////////////////////////////////////////
        'paths' => [
            // Emplacement où sont stoqués les documents Oscar
            'document_oscar' => realpath(__DIR__) . '/../../../documents/activity/',

            // Emplacement où sont stoqués les documents administratifs Oscar
            'document_admin_oscar' => realpath(__DIR__) . '/../../../documents/public/',

            // Modèle de feuille de temps
            'timesheet_modele' => realpath(__DIR__.'/../../data/timesheet_model.xls'),
        ],

         // Système de recherche
        'strategy' => [
            'activity' => [
                'search_engine' => [
                    // Elasticsearch
                    'class' => \Oscar\Strategy\Search\ActivityElasticSearch::class,
                    'params' => [['oscar_dev_elasticsearch_spartan:9200']]
                ]
            ],
            'person' => [
                'search_engine' => [
                    // Elasticsearch
                    'class' => \Oscar\Strategy\Search\PersonElasticSearch::class,
                    'params' => [['oscar_dev_elasticsearch_spartan:9200']]
                ]
            ],
            'organization' => [
                'search_engine' => [
                    // Elasticsearch
                    'class' => \Oscar\Strategy\Search\OrganizationElasticSearch::class,
                    'params' => [['oscar_dev_elasticsearch_spartan:9200']]
                ]
            ]
        ],

        ////////////////////////////////////////////////////////////////////////
        //
        // CONNECTORS (Optionnel)
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
            'organization' => [],
            'person' => []
        ],

        /*** Notifications ***/
        'notifications' => [
            // L'utilisateur peut configurer la fréquence des notifications
            'override' => false,

            // Envoi automatique
            'fixed' => ['Mer8', 'Lun20'] // ex: IMPOSE une notification chaque mercredis à 8 heure et Lundis à 20 heures
        ],

        /**********************************/
        /*** Qualification des dépenses ***/

        // Plan comptable général par défaut (standard)
        // Utilisé pour initialiser le plan comptable (modifiable ensuite
        // vie l'interface d'administration)
        'spenttypesource' => dirname(__DIR__).'/../install/plan-comptable.csv',

        // Masses
        'spenttypeannexes' => [
            "F" => "Fonctionnement",
            "I" => "Investissement",
            "P" => "Personnel"
        ],
        /**********************************/


        /*** Système d'envoi des mails ***/
        // Utilisé pour la génération des URLs dans les mails en ligne de commande
        'urlAbsolute' => 'http://localhost:8080',

        'mailer' => [
            'transport' => [
                'type' => 'file',
                'path' => realpath(__DIR__.'/../../../mails'),
            ],

            // Expéditeur
            'from' => [ 'oscar-bot@unicaen.fr' => 'Oscar Bot'],

            // Envoi d'une copy (Non effectif)
            'copy' => ['stephane.bouvry@unicaen.fr'],

            // Envoi activé
            'send' => true,
            'send_false_exception' => [],

            // Préfixe ajouté dans les sujets
            'subjectPrefix' => '[OSCAR DEV] ',

            // Emplacement du fichier de layout pour les mails
            'template' => realpath(__DIR__.'/../../module/Oscar/view/mail.phtml'),

            // Mails utilisé pour les tests / rapport
            'administrators' => ['stephane.bouvry@unicaen.fr', 'jacksay14@gmail.com']
        ]
    ],

    // Accès BDD
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                // Postgresql
                'driverClass' => '\Doctrine\DBAL\Driver\PDOPgSql\Driver',
                ////////////////////////////////////////////////////////////////

                'doctrine_type_mappings' => array(
                    'enum' => 'string'
                ),

                ////////////////////////////////////////////////////// CONNEXION
                // Exemple
                'params' => array(
                    'host'=>'oscar_dev_postgresql_spartan',
                    'port'     => '5432',
                    'user'     => 'oscar',
                    'password' => 'azerty',
                    'dbname'   => 'oscar_dev',
                    'charset'  => 'utf8'
                ),
            ),
        ),
    ),
);
