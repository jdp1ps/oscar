<?php
return array(
    'translator' => array(
        'locale' => 'fr_FR',
    ),

    ////////////////////////////////////////////////////////////////////////////
    /// CONFIGURATION PAR DÉFAUT
    /// Vous pouvez surcharger ces paramètres en les redéclarants dans le
    /// fichier local.php
    'oscar' => [
        // Par défaut, pas de socket
        'socket' => false,

        // ./doc/connectors.md
        'connectors' => [
            'organization' => [],
            'person' => []
        ],

        'closedDays' => function(&$joursFeries, $annee, $mois){
            $feries = [
                '1' => [
                    "$annee-1-1"    => 'Jour férié  (Nouvel an)',
                ],
                '2' => [],
                '3' => [],
                '4' => [],
                '5' => [
                    "$annee-5-1"     => 'Jour férié (Fête du travail)',
                    "$annee-5-8"    => 'Jour ferié (Armistice 39-45)',
                ],
                '6' => [],
                '7' => [
                    "$annee-7-14"    => 'Jour ferié (Fête nationnale)',
                ],
                '8' => [
                    "$annee-8-15"    => 'Jour ferié (Assomption)',
                ],
                '9' => [],
                '10' => [],
                '11' => [
                    "$annee-11-1"  => 'Jour ferié (La Toussaint)',
                    "$annee-11-11"  => 'Jour ferié (Armistice 14-18)',
                ],
                '12' => [
                    "$annee-12-25"   => 'Jour ferié (Noël)',
                ]
            ];

            // TODO Lundi de pâque
            // TODO Jeudi de l'assension
            // TODO Lundi de pentcôte

            foreach ($feries[$mois] as $jour => $message){
                $joursFeries[$jour] = $message;
            }
        },

        'closedDaysExtras' => function($locked, $year, $month){},

        // Mode de déclaration
        // FALSE => en pourcentage
        // TRUE => en heure
        'declarationsHours' => false,

        'declarationsWeekend' => false,
        'declarationsWeekendOverwrite' => false,

        // Authorise la personnalisation du mode de déclaration
        'declarationsHoursOverwriteByAuth' => false,

        // Durée standard d'une journée pour les déclarants (général)
        'declarationsDayDuration' => 7.4,
        'declarationsDayDurationMaxlength' => 9.0,

        // Durée standards d'une semaine
        'declarationsWeekDuration' => 7.4*5,
        'declarationsWeekDurationMaxlength' => 39.0,

        'declarationsMonthDuration' => 144.0,
        'declarationsMonthDurationMaxlength' => 176.0,

        // Configuration des cas particuliers (LABS)
        'declarationsDurations' => [

            'dayLength'     => [
                'value' => 8.0,
                'max' => 10.0,
                'userChange' => false,
                'days' => [
                    '1' => 7.5,
                    '2' => 7.5,
                    '3' => 7.5,
                    '4' => 7.5,
                    '5' => 7.0,
                    '6' => 0.0,
                    '7' => 0.0,
                ]
            ],

            'weekLength'     => [
                'value' => 37.0,
                'max' => 44.0,
                'userChange' => false
            ],

            'monthLength' => [
                'value' => 144.0,
                'max'   => 176.0,
                'userChange' => false
            ],

            'weekExceptions' => [
                '3'         => 3.0,
            ],
        ],



        'horslots' => [
            'conges' => [ 'code' => 'conges',  'label' => 'Congés',  'description' => 'Congès, RTT, récupération', 'icon' => true ],
            'training' => [ 'code' => 'training',  'label' => 'Formation',  'description' => 'Vous avez suivi un formation, DIFF, etc...', 'icon' => true ],
            'teaching' => [ 'code' => 'teaching',  'label' => 'Enseignement',  'description' => 'Cours, TD, fonction pédagogique', 'icon' => true ],
            'sickleave' => [ 'code' => 'sickleave', 'label' => 'Arrêt maladie',  'description' => '', 'icon' => true ],
            //  'absent' => [ 'code' => 'absent',  'label' => 'Absent',  'description' => '', 'icon' => true ],
            'research' => [ 'code' => 'research', 'label' => 'Autre recherche',  'description' => 'Autre projet de recherche (sans feuille de temps)', 'icon' => true ],
            'other' => [ 'code' => 'other', 'label' => 'Divers',  'description' => 'Autre activité', 'icon' => true ],
        ],


        ////////////////////////////////////////////////////////////// DOCUMENTS
        // Emplacement des dossiers pour les documents
        'paths' => [
            // Documents des activités
            'document_oscar' => realpath( __DIR__.'/../../data/documents/activity'),

            // Documents 'publiques"
            'document_admin_oscar' => realpath( __DIR__.'/../../data/documents/public'),

            // Modèle de feuille de temps
            'timesheet_modele' => realpath(__DIR__.'/../../data/timesheet_model.xls'),
        ],

        'generated-documents' => [
            'activity' => []
        ],

        // Notification par défaut
        /*** Notifications ***/
        'notifications' => [
            // Envoi automatique (ex: Lun8 (Lundis à 8 heure), Mer22 (Mercredis à 22 heures)
            'fixed' => []
        ],

        'urlAbsolute' => 'http://localhost:8080',

        // Système d'envoi des mails
        'mailer' => [
            'transport' => [
                'type' => 'file',
                'path' => realpath(__DIR__.'/../../data/mails'),
            ],
            'administrators' => [],
            'from' => [ 'oscar-bot@oscar.fr' => 'Oscar Bot'],
            'copy' => [],
            'send' => false,
            'send_false_exception' => [],
            'template' => realpath(__DIR__.'/../../module/Oscar/view/mail.phtml'),
            'subjectPrefix' => '[OSCAR DEV]'
        ],

        ////////////////////////////////////////////////////////////////////////
        // Validation des données
        'validation' => [
            // ------------------------------------------ Validation du code PFI
            // Il s'agit de l'expression régulière utilisée par Oscar pour
            // vérifier la validité formelle du PFI saisi.
            // ex: 209ED2024
            'pfi' => '/^[0-9]{3}[A-Z]{2,3}[0-9]{2,4}$/mi'
        ]
    ],

    // Configuration de la base de données
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                // Le drivers peut changer en production
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'doctrine_type_mappings' => array(
                    'enum' => 'string'
                ),
            ),
        ),

        'configuration' => array(
            'orm_default' => array(
                'metadata_cache'    => 'array',
                'query_cache'       => 'array',
                'result_cache'      => 'array',
                'driver'            => 'oscar_entities',
                'generate_proxies'  => true,
                'proxy_dir'         => 'data/DoctrineORMModule/Proxy',
                'proxy_namespace'   => 'DoctrineORMModule\Proxy',
                'filters'           => array(),
                'numeric_functions' => [
                    'timestampdiff'  =>  'Oro\ORM\Query\AST\Functions\Numeric\TimestampDiff',
                    'dayofyear'  =>      'Oro\ORM\Query\AST\Functions\SimpleFunction',
                    'dayofmonth'  =>     'Oro\ORM\Query\AST\Functions\SimpleFunction',
                    'dayofweek'  =>      'Oro\ORM\Query\AST\Functions\SimpleFunction',
                    'week'  =>           'Oro\ORM\Query\AST\Functions\SimpleFunction',
                    'day'  =>            'Oro\ORM\Query\AST\Functions\SimpleFunction',
                    'hour'  =>           'Oro\ORM\Query\AST\Functions\SimpleFunction',
                    'minute'  =>         'Oro\ORM\Query\AST\Functions\SimpleFunction',
                    'month'  =>          'Oro\ORM\Query\AST\Functions\SimpleFunction',
                    'quarter'  =>        'Oro\ORM\Query\AST\Functions\SimpleFunction',
                    'second'  =>         'Oro\ORM\Query\AST\Functions\SimpleFunction',
                    'year'  =>           'Oro\ORM\Query\AST\Functions\SimpleFunction',
                ]
            ),
        ),
        'driver' => array(
            'oscar_entities' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../../module/Oscar/src/Oscar/Entity',
                ),
            ),
        ),
    ),
);
