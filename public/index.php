<?php
/*
function mojoDebugger($level, $error, $file, $line, $context) {
    $levels = [E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error'];

    $level = (isset($levels[$level]) ? $levels[$level] : 'Unknown Level [number ' . $level . ']');
    $error = (empty($error) ? 'Unknown error' : $error);
    $file = (empty($file) ? '[unknown file]' : $file);
    $line = (empty($line) ? '0 or unknown' : $line);

    echo $level . ': ' . $error . ' in ' . $file . ' on line ' . $line . "\n\n";

    echo "Backtrace:\n";

    $backtrace = debug_backtrace();
    array_shift($backtrace); // No need to report the debugger itself
    $backtrace = array_reverse($backtrace, True);

    foreach($backtrace as $call => $info) {
        echo "\t" . $call . '. ';

        $func = (empty($info['function']) ? '' : $info['function']);

        if (empty($info['args'])) {
            echo $func . '() ';
        } else {
            $info['args'] = print_r($info['args'], 1);
            $info['args'] = str_replace(["\n", "\t"], '', $info['args']);
            $info['args'] = substr($info['args'], 6);
            $info['args'] = substr($info['args'], 0, -1);
            $info['args'] = trim(str_replace('    ', ' ', $info['args']));
            echo $func . '(' . (strlen($info['args']) > 128 ? substr($info['args'], 0, 128) . '...' : $info['args']) . ') ';
        }

        echo (empty($info['file']) ? '[unknown file]' : $info['file']);
        echo (empty($info['line']) ? ':0 or unknown' : ':' . $info['line']);
        echo "\n";
    }
}

set_error_handler('mojoDebugger', E_ALL | E_WARNING | E_NOTICE | E_STRICT | E_PARSE);
*/
if( file_exists(__DIR__.'/../MAINTENANCE') ){
    include 'maintenance.html';
    exit;
}

define('APP_DIR', realpath( __DIR__.'/../' ));

// On test la variable d'environnement issue de apache
if (function_exists('apache_getenv') && ($apacheEnv = apache_getenv('APPLICATION_ENV'))) {
    putenv('APPLICATION_ENV=' . apache_getenv('APPLICATION_ENV'));
}


// Debug bar
define('REQUEST_MICROTIME', microtime(true));

// Default ENV (development) pour les buildt-in server
if( !getenv('APPLICATION_ENV') && php_sapi_name() === 'cli-server' ){
    putenv('APPLICATION_ENV=development');
}
elseif( !getenv('APPLICATION_ENV' )){
    putenv('APPLICATION_ENV=production');
}

// Servir normalement les fichiers effectif
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

if( getenv('APPLICATION_ENV') == 'development' ){
    define('DEBUG_OSCAR', true);
    error_reporting(E_ALL ^ E_DEPRECATED);
} else {
    define('DEBUG_OSCAR', false);
}

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Setup autoloading
require __DIR__.'/../vendor/autoload.php';

// Run the application!

$conf = require 'config/application.config.php';

try {
    $app = Zend\Mvc\Application::init(require 'config/application.config.php');
    $app->run();
} catch (\Oscar\Exception\OscarException $e ){
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>OSCAR FAILURE</title>
        <style>
            body { font-family: Arial; color: white:
                background: #990000;}
        </style>
    </head>
    <body>
    
    </body>
    </html>
    <?php
}
