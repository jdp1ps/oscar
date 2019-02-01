<?php
$config = array(
    'modules' => array(
        'ZfcBase',
        'DoctrineModule',
        'DoctrineORMModule',
        'ZfcUser',
        'ZfcUserDoctrineORM',
        'BjyAuthorize',
        //'ZendDeveloperTools',
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
    // $config['modules'][] = 'ZendDeveloperTools';
}


if( !\Zend\Console\Console::isConsole() ){
  // $config['modules'][] = 'BjyAuthorize';
}


return $config;
