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

        'theme' => 'oscar',
        'themes' => ['oscar', 'adaj', 'debug'],

        // Pour les opérations automatique, permet d'obtenir depuis la base de donnée la devise à utiliser
        // dans les activités de recherche si rien n'est spécifié. (valeur de la colonne 'label').
        'defaultCurrency' => 'Euro',

        /////////////////////////////////////////////////////////////////////////////////// oscar_num_separator [string]
        /// Permet de modifier le formalisme de la numérotation automatique dans Oscar, il faut également modifier
        ///  la fonction Postgresql associée (doc/numerotation.md)
        ///
        'oscar_num_separator' => 'DRI',

        ////////////////////////////////////////////////////////////////////////////////// authPersonNormalize [boolean]
        /// Permet d'ignorer la casse lors de la jonction entre les personnes et l'authentification
        'authPersonNormalize' => false,


        // Active l'option autorisant l'importation de calendrier pour les déclarants
        'importEnable' => true,


        'export' => [
            'payments' => [
                'separator' => '$$',
                'persons' => '',
                'organizations' => 'Composante responsable,Laboratoire,Financeur'
            ]
        ],


        ////////////////////////////////////////////////////////////////////// listPersonIncludeActivityMember [boolean]
        /// Lorsque qu'un membre d'une organisation disposant du privilège "Liste des personnes"
        /// accède à la liste du personnel de son organisation, cette denière ne propose que les personnes
        /// qualifiées avec un rôle dans l'oganisation. Cette option permet d'ajouter à cette liste
        /// les personnes impliquées dans des activités où la structure endosse un rôle principal.
        ///
        'listPersonIncludeActivityMember' => false,

        'allow_activity_request' => 'leader',

        ////////////////////////////////////////////////////////////////////// connectors [array]
        // Voir documentation ./doc/connectors.md
        'connectors' => [
            'organization' => [],
            'person' => []
        ],

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// Champs masqués dans la fiche activité (Saisie)
        'activity_hidden_fields' => [],

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// JOURS FERIÉS
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

            // Lundi de pâque
            $easterDate  = easter_date($annee);
            $easterDay   = (int)date('j', $easterDate) + 1;
            $easterMonth = date('n', $easterDate);
            $easterYear   = date('Y', $easterDate);
            $feries[$easterMonth][sprintf("%s-%s-%s", $easterYear, $easterMonth, $easterDay)] = "Jour ferié (Lundi de Pâques)";

            // Jeudi de l'assension
            $ascension = new DateTime(date('Y-m-d', $easterDate));
            $ascension->add(new DateInterval('P39D'));
            $feries[$ascension->format('n')][$ascension->format('Y-n-j')] = "Jour ferié (Jeudi de l'ascension)";

            // Pentecôte
            $pentecote = new DateTime(date('Y-m-d', $easterDate));
            $pentecote->add(new DateInterval('P50D'));
            $feries[$pentecote->format('n')][$pentecote->format('Y-n-j')] = "Jour ferié (Pentecôte)";

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

        // Modification des horaires de la personne
        'declarationsScheduleOverwrite' => false,
        'declarationsScheduleOverwriteValidation' => 'n+1', // OU via le privilège PERSON_MANAGE_SCHEDULE

        // Durée standard d'une journée pour les déclarants (général)
        'declarationsDayDuration' => 7.4,
        'declarationsDayDurationMaxlength' => 9.0,

        // Indication visuelle de dépassement problématique
        // Journée de 8.0 heures => déclaré 8.0*.5 = 4.0
        'declarationAmplitudeMin' => .75,

        // Journée de 8.0 heures => 8.0*1.125 = 9.0
        'declarationAmplitudeMax' => 1.25,

        // Durée standards d'une semaine
        'declarationsWeekDuration' => 7.4*5,


        'declarationsWeekDurationMaxlength' => 39.0,

        'declarationsMonthDuration' => 144.0,
        'declarationsMonthDurationMaxlength' => 176.0,

        'userSubmitSchedule' => false,
        'userSubmitScheduleValidateByNp1' => true,
        'userSubmitScheduleValidateByPrivilege' => \Oscar\Provider\Privileges::PERSON_MANAGE_SCHEDULE,

        // Niveau d'accès à la liste du personnel
        //  - 0
        //  - 1 = N=1
        //  - 2 = Personnel dans l'oganisation
        //  - 3 = Person dans l'organisation, et dans les activités
        'listPersonnel' => 0,

        // Modèles horaires
        'scheduleModeles' => [
            // A définir dans local.php
        ],

        'horslots' => [
//            'conges' => [ 'code' => 'conges',  'label' => 'Congés',  'description' => 'Congès, RTT, récupération', 'icon' => true ],
//            'training' => [ 'code' => 'training',  'label' => 'Formation',  'description' => 'Vous avez suivi un formation, DIFF, etc...', 'icon' => true ],
//            'teaching' => [ 'code' => 'teaching',  'label' => 'Enseignement',  'description' => 'Cours, TD, fonction pédagogique', 'icon' => true ],
//            'sickleave' => [ 'code' => 'sickleave', 'label' => 'Arrêt maladie',  'description' => '', 'icon' => true ],
//            //  'absent' => [ 'code' => 'absent',  'label' => 'Absent',  'description' => '', 'icon' => true ],
//            'research' => [ 'code' => 'research', 'label' => 'Autre recherche',  'description' => 'Autre projet de recherche (sans feuille de temps)', 'icon' => true ],
//            'other' => [ 'code' => 'other', 'label' => 'Divers',  'description' => 'Autre activité', 'icon' => true ],
        ],


        ////////////////////////////////////////////////////////////// DOCUMENTS
        // Emplacement des dossiers pour les documents
        'paths' => [
            // Documents des activités
            'document_oscar' => realpath( __DIR__.'/../../data/documents/activity/'),

            // Documents des demandes d'activités
            'document_request' => realpath( __DIR__.'/../../data/documents/request'),

            // Documents 'publiques"
            'document_admin_oscar' => realpath( __DIR__.'/../../data/documents/public/'),

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

//        // Système d'envoi des mails
//        'mailer' => [
//            'transport' => [
//                'type' => 'file',
//                'path' => realpath(__DIR__.'/../../data/mails'),
//            ],
//            'administrators' => [],
//            'from' => [ 'oscar-bot@oscar.fr' => 'Oscar Bot'],
//            'copy' => [],
//            'send' => false,
//            'send_false_exception' => [],
//            'template' => realpath(__DIR__.'/../../module/Oscar/view/mail.phtml'),
//            'subjectPrefix' => '[OSCAR DEV]'
//        ],

        ////////////////////////////////////////////////////////////////////////
        // Validation des données
        'validation' => [
            // ------------------------------------------ Validation du code PFI
            // Il s'agit de l'expression régulière utilisée par Oscar pour
            // vérifier la validité formelle du PFI saisi.
            // ex: 209ED2024
            'pfi' => '/^([0-9]{3}[A-Z]{2,3}[0-9]{2,4})|([0-9]{3}C[0-9]{3}[A-Z]{1}[FPI]?)$/mi'
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
