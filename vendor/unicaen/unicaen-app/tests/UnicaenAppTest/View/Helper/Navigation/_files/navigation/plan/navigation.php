<?php
return array(
    'home' => array(
        'label' => "Accueil",
        'uri'   => 'home',
        'pages' => array(
            'etab' => array(
                'label' => "UCBN",
                'uri'   => 'http://www.unicaen.fr/',
            ),
            'apropos' => array(
                'label'   => "À propos",
                'uri'     => 'home/apropos',
                'visible' => false,
                'sitemap' => true,
            ),
            'non-inclus-1' => array(
                'label'   => "Non inclus 1",
                'uri'     => 'home/non-inclus-1',
                'visible' => false,
            ),
            'non-inclus-2' => array(
                'label'   => "Non inclus 2",
                'uri'     => 'home/non-inclus-2',
                'visible' => false,
                'pages' => array(
                    'a' => array(
                        'label' => "A",
                        'uri'   => 'home/non-inclus-2/a',
                        'visible' => false,
                        'sitemap' => true,
                    ),
                ),
            ),
        ),
    ),
);