<?php

$unicaenCodeDir = dirname(__DIR__);

return [
    'unicaen-code' => [
        'sqlformatter-file'    => $unicaenCodeDir . '/vendor/sql-formatter-master/lib/SqlFormatter.php',
        'geshi-file'           => $unicaenCodeDir . '/vendor/geshi/geshi.php',
        'view-dirs'            => [$unicaenCodeDir . '/code'],
        'template-dirs'        => [$unicaenCodeDir . '/code/template'],
        'generator-output-dir' => '/tmp/UnicaenCode',
        'namespaces'           => [
            'services'  => [],
            'forms'     => [],
            'hydrators' => [],
            'entities'  => [],
        ],
    ],

    'controllers' => [
        'invokables' => [
            'UnicaenCode\Controller' => UnicaenCode\Controller\Controller::class,
        ],
    ],

    'service_manager' => [
        'invokables' => [
            'UnicaenCode\Config'        => UnicaenCode\Service\Config::class,
            'UnicaenCode\Collector'     => UnicaenCode\Service\Collector::class,
            'UnicaenCode\Introspection' => UnicaenCode\Service\Introspection::class,
            'UnicaenCode\CodeGenerator' => UnicaenCode\Service\CodeGenerator::class,
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'unicaen-code/index' => $unicaenCodeDir . '/view/unicaen-code/index.phtml',
            'zend-developer-tools/toolbar/unicaen-code'
                                 => $unicaenCodeDir . '/view/zend-developer-tools/toolbar/unicaen-code.phtml',
        ],
    ],

    'router'             => [
        'routes' => [
            'unicaen-code' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/unicaen-code[/:view]',
                    'defaults' => [
                        'controller' => 'UnicaenCode\Controller',
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    // intégration à la Zend Developer Toolbar
    'zenddevelopertools' => [
        'profiler' => [
            'collectors' => [
                'unicaen-code_collector' => 'UnicaenCode\Collector',
            ],
        ],
        'toolbar'  => [
            'entries' => [
                'unicaen-code_collector' => 'zend-developer-tools/toolbar/unicaen-code',
            ],
        ],
    ],

    // Intégration à BjyAuthorize
    'bjyauthorize'       => [
        'guards' => [
            'BjyAuthorize\Guard\Controller' => [
                ['controller' => 'UnicaenCode\Controller', 'roles' => []],
            ],
        ],
    ],
];