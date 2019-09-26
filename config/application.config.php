<?php
$config = array(
    'modules' => array(
        'Zend\Cache',
        'Zend\Filter',
        'Zend\Form',
        'Zend\Hydrator',
        'Zend\I18n',
        'Zend\InputFilter',
        'Zend\Log',
        'Zend\Mail',
        'Zend\Mvc\Console',
        'Zend\Mvc\I18n',
//    'Zend\Mvc\Plugin\FilePrg',
        'Zend\Mvc\Plugin\FlashMessenger',
//    'Zend\Mvc\Plugin\Identity',
        'Zend\Mvc\Plugin\Prg',
        'Zend\Navigation',
        'Zend\Paginator',
        'Zend\Router',
        'Zend\Session',
        'Zend\Validator',

        'DoctrineModule',
        'DoctrineORMModule',
        'ZfcUser',
//        'ZfcUserDoctrineORM',
        'BjyAuthorize',
        'UnicaenApp',
        'UnicaenAuth',
        'Oscar',
    ),

    'module_listener_options' => array(
        'config_glob_paths'    => array(
            'config/autoload/{,*.}{global,local}.php',
        ),
        'module_paths' => array(
            './module',
            './vendor',
        ),
    ),
);

if (\Zend\Console\Console::isConsole()) {
    unset($config['modules'][array_search('BjyAuthorize', $config['modules'])]);
}

if( getenv('APPLICATION_ENV') == 'development' ){
    $config['modules'][] = 'ZendDeveloperTools';
}


if( !\Zend\Console\Console::isConsole() ){
  // $config['modules'][] = 'BjyAuthorize';
}


return $config;
