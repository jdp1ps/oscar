<?php

namespace UnicaenCalendrier;

use Laminas\Router\Http\Literal;
use UnicaenCalendrier\Controller\IndexController;
use UnicaenCalendrier\Controller\SignatureControllerFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenSignature\Controller\SignatureController;

return [
//    'bjyauthorize' => [
//        'guards' => [
//            PrivilegeController::class => [
//                [
//                    'controller' => IndexController::class,
//                    'action' => [
//                        'index',
//                    ],
////                    'privileges' => [
////                        Unicaencalendrier_indexPrivileges::INDEX,
////                    ],
//                    'roles' => []
//                ],
//            ],
//        ],
//    ],
//
//    'router' => [
//        'routes' => [
//            'unicaen-calendrier' => [
//                'type' => Literal::class,
//                'options' => [
//                    'route' => '/unicaen-calendrier',
//                    'defaults' => [
//                        'controller' => IndexController::class,
//                        'action' => 'index',
//                    ],
//                ],
//                'may_terminate' => true,
//                'child_routes' => [],
//            ],
//        ],
//    ],

    'service_manager' => [
        'factories' => [],
    ],
    'controllers' => [
        'factories' => [
            SignatureController::class => SignatureControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [],
    ],
    'hydrators' => [
        'factories' => [],
    ]

];