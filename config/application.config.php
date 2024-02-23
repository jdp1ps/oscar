<?php
$config = array(
    'modules' => array(

        'Laminas\Cache',
        'Laminas\Filter',
        'Laminas\Form',
        'Laminas\Hydrator',
        'Laminas\I18n',
        'Laminas\InputFilter',
        'Laminas\Log',
        'Laminas\Mail',
        'Unicaen\Console',
        'Laminas\Mvc\I18n',
        'Laminas\Mvc\Plugin\FlashMessenger',
        'Laminas\Mvc\Plugin\Prg',
        'Laminas\Navigation',
        'Laminas\Paginator',
        'Laminas\Router',
        'Laminas\Session',
        'Laminas\Validator',
        'DoctrineModule',
        'DoctrineORMModule',
        'ZfcUser',
        'BjyAuthorize',
        'UnicaenApp',
        'UnicaenAuthentification',
        'UnicaenPrivilege',
        'UnicaenUtilisateur',
        'Oscar',
        'UnicaenSignature'
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

if (PHP_SAPI == 'cli') {
    unset($config['modules'][array_search('BjyAuthorize', $config['modules'])]);
}

if( getenv('APPLICATION_ENV') == 'development' ){
    $config['modules'][] = 'Laminas\DeveloperTools';
}

return $config;
