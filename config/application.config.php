<?php
$config = array(
    'modules' => array(
        'CentaureSync',
        'ZfcBase',
        'DoctrineModule',
        'DoctrineORMModule',
        'ZfcUser',
        'ZfcUserDoctrineORM',
        'BjyAuthorize',
        'UnicaenApp',
        'UnicaenAuth',
        'UnicaenCode',
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

if( getenv('APPLICATION_ENV') == 'development' ){
    $config['modules'][] = 'ZendDeveloperTools';
}


if( !\Zend\Console\Console::isConsole() ){
  // $config['modules'][] = 'BjyAuthorize';
}


return $config;
