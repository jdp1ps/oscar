<?php

namespace <module>;

use UnicaenAuth\Guard\PrivilegeController;

return [

    /* Routes (à personnaliser) */
    'router'          => [
        'routes' => [
            '<route>' => [
                'type'          => 'Literal',
                'options'       => [
                    'route'    => '/<route>',
                    'defaults' => [
                        '__NAMESPACE__' => '<module>\Controller',
                        'controller' => '<name>',
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    /* Placez ici vos routes filles */
                ],
            ],
        ],
    ],

    /* Exemple de menu (à personnaliser) */
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    '<route>' => [
                        'label'    => "<name>",
                        'route'    => '<route>',
                        'resource' => PrivilegeController::getResourceId('<module>\Controller\<name>','index'),
                    ],
                ],
            ],
        ],
    ],

    /* Droits d'accès */
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => '<module>\Controller\<name>',
                    'action'     => ['index'],
                    'privileges' => [
                        /* Placez ici les pricilèges concernés */
                    ],
                ],
            ],
        ],
    ],

    /* Déclaration du contrôleur */
    'controllers'     => [
        'invokables' => [
            '<module>\Controller\<name>' => <wmClassname>::class,
        ],
    ],
];