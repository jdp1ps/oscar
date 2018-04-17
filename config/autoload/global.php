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
