<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:44
 */
require __DIR__.'/../vendor/autoload.php';

$console = new \Symfony\Component\Console\Application();
$conf = require __DIR__.'/../config/application.config.php';
$app = Zend\Mvc\Application::init($conf);

$commands = [];
// On parse automatiquement le dossier des commandes
$path = __DIR__.'/../module/Oscar/src/Oscar/Command';
function scanCommandFiles($dir){
    global $app;
    $output = [];
    $re = '/.*Command\.php/m';
    $scan = scandir($dir);
    foreach ($scan as $key => $value) {
        if( !in_array($value, ['.', '..']) ){
            if( is_dir($dir.DIRECTORY_SEPARATOR.$value) ){

            } else {

                if( preg_match($re, $value, $matches) ){
                    $class = substr($value, 0, strlen($value)-4);
                    $reflector = new ReflectionClass('Oscar\Command\\'.$class);
                    if( $reflector->isSubclassOf(\Symfony\Component\Console\Command\Command::class) ){
                        $result[] = $reflector->newInstanceArgs([$app->getServiceManager()]);
                    }
                }
            }
        }
    }
    return $result;
}
$commands = scanCommandFiles($path);
$console->addCommands($commands);
$console->run();