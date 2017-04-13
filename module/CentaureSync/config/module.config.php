<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 20/10/15 15:12
 * @copyright Certic (c) 2015
 */
return [
    'console' => [
        'router' => [
            'routes' => [
                'oscar_sync' => [
                    'options' => [
                        'route' => 'centaure sync [--verbose|-v] [--silent|-s] [--simulate] <doWhat>',
                        'defaults' => [
                            'controller' => 'Sync',
                            'action' => 'sync',
                        ],
                    ],
                ],
            ],
        ],
    ],

    'bjyauthorize' => [
        'guards' => [
            'BjyAuthorize\Guard\Controller' => [
                [ 'controller' => 'Sync', 'roles'=>[] ],
            ]
        ]
    ],

    'controllers' => [
        'invokables' => [
            'Sync' => \CentaureSync\Controller\SyncController::class,
        ]
    ]
];