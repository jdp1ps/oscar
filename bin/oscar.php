<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:44
 */
require __DIR__.'/../vendor/autoload.php';

// Fix PATH problems
chdir(dirname(__DIR__));

$console = new \Symfony\Component\Console\Application();
$conf = require __DIR__.'/../config/application.config.php';
$app = \Laminas\Mvc\Application::init($conf);

$commands = [];

$paths = $app->getConfig()['console_commands'];

function scanCommandFiles(string $namespace, string $dir){
    global $app;
    $output = [];
    $re = '/.*Command\.php/m';

    if( !file_exists($dir) ){
        echo ("[WARNING] Command path : Le dossier '$dir' n'existe pas\n");
        return [];
    }

    $scan = scandir($dir);
    foreach ($scan as $key => $value) {
        if( !in_array($value, ['.', '..']) ){
            if( is_dir($dir.DIRECTORY_SEPARATOR.$value) ){

            } else {
                if( preg_match($re, $value, $matches) ){
                    $class = substr($value, 0, strlen($value)-4);
                    $reflector = new ReflectionClass($namespace.$class);
                    if( $reflector->isSubclassOf(\Symfony\Component\Console\Command\Command::class) ){
                        $instance = $reflector->newInstanceArgs([$app->getServiceManager()]);
                        if( property_exists($instance, 'disabled') ) {
                            continue;
                        }
                        $result[] = $instance;
                    }
                }
            }
        }
    }
    return $result;
}

foreach ($paths as $namespace=>$path) {
    $commands = scanCommandFiles($namespace, $path);
    $console->addCommands($commands);
}

$console->run();