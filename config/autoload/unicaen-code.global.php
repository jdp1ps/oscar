<?php

$settings = [
    'view-dirs'     => [getcwd() . '/code'],
    'template-dirs' => [getcwd() . '/code/template'],
    'generator-output-dir' => '/tmp/UnicaenCode',
    'namespaces'           => [
        'services'  => [
            'Oscar',
        ],
        'forms'     => [
            'Oscar\Form',
        ],
        'hydrators' => [
            'Oscar\Hydrator',
        ],
        'entities'  => [
            'Oscar\Entity',
        ],
    ],
];

return [
    'unicaen-code' => $settings,
];