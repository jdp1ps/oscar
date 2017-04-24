<?php
// Application config
$appConfig = [];//include __DIR__ . '/../../../../../config/application.config.php';

// Test config
$testConfig = [
    'modules' => [
        'UnicaenAuth'
    ],
    'module_listener_options' => [
        'config_glob_paths'    => [
            __DIR__ . '/autoload/{,*.}{global,local}.php',
        ],
    ],
];

return \Zend\Stdlib\ArrayUtils::merge($appConfig, $testConfig);
