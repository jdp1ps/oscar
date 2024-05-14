<?php
return array(
    'view_manager' => array(
        'display_not_found_reason' => getenv('APPLICATION_ENV') == 'development',
        'display_exceptions' => getenv('APPLICATION_ENV') == 'development',
    ),

    // Oscar
    'oscar' => [
        // Oscar Live
        'socket' => false,

        // gearman
        'gearman-job-server-host' => '£CONTAINER_GEARMAN',

        ////////////////////////////////////////////////////////////////////////
        // ! EXPERIMENTAL !
        'generated-documents' => [],

        'horslots' => [
            ////////////////////////////////// EXEMPLE de CONFIGURATION DES HORS-LOTS
            'conges' => ['code' => 'conges', 'label' => 'Congés', 'group' => 'abs', 'description' => 'Congès/RTT', 'icon' => true],
            'teaching' => ['code' => 'teaching', 'label' => 'Enseignement', 'group' => 'education', 'description' => 'Cours', 'icon' => true],
            'sickleave' => ['code' => 'sickleave', 'label' => 'Arrêt maladie', 'group' => 'abs', 'description' => '', 'icon' => true],
            'research' => ['code' => 'research', 'label' => 'Autre recherche', 'group' => 'research', 'description' => 'Recherche hors-contrat', 'icon' => true],
            'other' => ['code' => 'other', 'label' => 'Divers', 'group' => 'other', 'description' => 'Autre activité', 'icon' => true],
        ],


        // Répartition horaire
        'declarationsDurations' => [
            'dayLength' => [
                'value' => 7.5,
                'max' => 10.0,
                'min' => 5.0,
                'days' => ['1' => 8.0, '2' => 8.0, '3' => 8.0, '4' => 8.0, '5' => 8.0, '6' => 0.0, '7' => 0.0]
            ],

            'weekLength' => [
                'value' => 37.0,
                'max' => 44.0,
                'min' => 20.0,
                'userChange' => false
            ],

            'monthLength' => [
                'value' => 144.0,
                'max' => 184.0,
                'min' => 80.0,
                'userChange' => false
            ],

            'weekExceptions' => [
                '3' => 3.0,
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
            'timesheet_modele' => realpath(__DIR__ . '/../../data/timesheet_model.xls'),
        ],

        // Système de recherche
        'strategy' => [
            'activity' => [
                'search_engine' => [
                    // Elasticsearch
                    'class' => \Oscar\Strategy\Search\ActivityElasticSearch::class,
                    'params' => [['£CONTAINER_ELASTICSEARCH:9200']]
                ]
            ],
            'person' => [
                'search_engine' => [
                    // Elasticsearch
                    'class' => \Oscar\Strategy\Search\PersonElasticSearch::class,
                    'params' => [['£CONTAINER_ELASTICSEARCH:9200']]
                ]
            ],
            'organization' => [
                'search_engine' => [
                    // Elasticsearch
                    'class' => \Oscar\Strategy\Search\OrganizationElasticSearch::class,
                    'params' => [['£CONTAINER_ELASTICSEARCH:9200']]
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
            'organization' => [
                'ldap' => [
                    'class' => \Oscar\Connector\ConnectorLdapOrganizationJson::class,
                    'params' => realpath(__DIR__) . '/../connectors/organization_ldap.yml',
                    'editable' => false
                ]
            ],
            'person' => [
                'ldap' => [
                    'class' => \Oscar\Connector\ConnectorLdapPersonJson::class,
                    'params' => realpath(__DIR__) . '/../connectors/person_ldap.yml',
                    'editable' => false
                ]
            ],
            'spent' => [
                'sifac' => [
                    'class' => \Oscar\Connector\ConnectorSpentSifacOCI::class,
                    'params' => [
                        'username' => 'OSCAR',
                        'password' => '**********',
                        'SID' => 'T26',
                        'port' => '****',
                        'hostname' => '**********',
                        'spent_query' => "select  MEASURE AS pfi,  RLDNR as AB9, STUNR as idsync,  awref AS numSifac, vrefbn as numCommandeAff, vobelnr as numPiece, LIFNR as numFournisseur, KNBELNR as pieceRef, fikrs AS codeSociete, BLART AS codeServiceFait, FAREA AS codeDomaineFonct, sgtxt AS designation, BKTXT as texteFacture, wrttp as typeDocument, TRBTR as montant, fistl as centreDeProfit, fipex as compteBudgetaire, prctr AS centreFinancier, HKONT AS compteGeneral, budat as datePiece, bldat as dateComptable, gjahr as dateAnneeExercice, zhldt AS datePaiement,  PSOBT AS dateServiceFait from sapsr3.v_fmifi where measure = '%s' AND rldnr='9A' AND MANDT='300' AND BTART='0250'"
                    ]
                ]
            ]
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
        'spenttypesource' => dirname(__DIR__) . '/../install/plan-comptable.csv',

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
                'type' => 'smtp',
                'host' => 'oscar_p1ps_mailhog',
                'port' => 1025,
                'username' => '',
                'password' => '',
                'security' => '',
            ],

            // Expéditeur
            'from' => ['si-recherche@univ-paris1.fr' => 'Oscar P1PS Bot'],

            // Envoi d'une copy (Non effectif)
            'copy' => ['si-recherche@univ-paris1.fr'],

            // Envoi activé
            'send' => true,
            'send_false_exception' => [],

            // Préfixe ajouté dans les sujets
            'subjectPrefix' => '[OSCAR DEV] ',

            // Emplacement du fichier de layout pour les mails
            'template' => realpath(__DIR__ . '/../../module/Oscar/view/mail.phtml'),

            // Mails utilisé pour les tests / rapport
            'administrators' => ['si-recherche@univ-paris1.fr']
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
                    'host' => '£CONTAINER_POSTGRESQL',
                    'port' => '5432',
                    'user' => 'oscar',
                    'password' => '*******',
                    'dbname' => 'oscar_dev',
                    'charset' => 'utf8'
                ),
            ),
        ),
    ),
);
