<?php

// Application config
$appConfig = array();//include __DIR__ . '/../../../../../config/application.config.php';

// Test config
$testConfig = array(
    'modules' => array(
        'UnicaenLdap'
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            __DIR__ . '/autoload/{,*.}{global,local}.php',
        ),
    ),
);

return \Zend\Stdlib\ArrayUtils::merge($appConfig, $testConfig);
