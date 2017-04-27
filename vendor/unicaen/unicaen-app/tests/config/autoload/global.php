<?php 
return array(
//    'translator' => array(
//        'locale' => 'fr_FR',
//        'translation_file_patterns' => array(
//            array(
//                'type'     => 'getdtext',
//                'base_dir' => __DIR__ . '/../language',
//                'pattern'  => '%s.mo',
//            ),
//        ),
//    ),
    'controllers' => array(
        'invokables' => array(
            'UnicaenAppTest\Controller\Plugin\TestAsset\Contact' => 'UnicaenAppTest\Controller\Plugin\TestAsset\ContactController',
        ),
    ),
);
