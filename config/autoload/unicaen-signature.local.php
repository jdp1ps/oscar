<?php
return [
    'unicaen-signature' => [
        'letterfiles' => [
            [
                'label'     => 'ESUP signature',
                'name'      => 'esup',
                'default'   => true,
                'class'     => \UnicaenSignature\Strategy\Letterfile\EsupLetterfileStrategy::class,
                'levels'    => [
                    'visa_visuel' => 'visa_visuel_in_esup',
                    'sign_visuel' => 'sign_visuel_in_esup',
                    'sign_certif' => 'sign_certif_in_esup',
                    'sign_eidas' => 'sign_eidas_in_esup',
                ],
                'config'    => [
                    // ESUP configuration
                    'url' => "https://esup.unicaen.fr/"
                ]
            ],
            [
                'label'     => 'OSCAR visa',
                'name'      => 'oscar',
                'default'   => false,
                'class'     => \UnicaenSignature\Strategy\Letterfile\OscarLetterfileStrategy::class,
                'levels'    => [
                    'visa_hidden' => 'visa_hidden',
                ],
                'config'    => [

                ]
            ]
        ]
    ]
];