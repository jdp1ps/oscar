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

// On test la variable d'environnement issue de apache
if (function_exists('apache_getenv') && ($apacheEnv = apache_getenv('APPLICATION_ENV'))) {
    putenv('APPLICATION_ENV=' . apache_getenv('APPLICATION_ENV'));
}

// Debug bar
define('REQUEST_MICROTIME', microtime(true));

// Default ENV (development) pour les buildt-in server
if( !getenv('APPLICATION_ENV') && php_sapi_name() === 'cli-server' ){
    if( array_key_exists('APPLICATION_ENV', $_ENV) ){
        putenv('APPLICATION_ENV=' . $_ENV['APPLICATION_ENV']);
    } else {
        putenv('APPLICATION_ENV=development');
    }
}
elseif( !getenv('APPLICATION_ENV' )){
    putenv('APPLICATION_ENV=production');
}

if( php_sapi_name() === 'cli-server' ){

    header("Access-Control-Allow-Origin: http://localhost:8081");
    header("Access-Control-Allow-Credentials: true");
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept');

}


// Servir normalement les fichiers effectif
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

if( php_sapi_name() !== 'cli' && file_exists(__DIR__.'/../MAINTENANCE') ){
	include 'maintenance.html';
	exit;
}

if( getenv('APPLICATION_ENV') == 'development' ){
    define('DEBUG_OSCAR', true);
    error_reporting(E_ALL);
} else {
    define('DEBUG_OSCAR', false);
    error_reporting(E_ERROR);
}

set_error_handler('oscar_exception');

// ON LOG LES PROBEMES
function oscar_exception($errno , $errstr, $errfile="UnknowFile", $errline=0, $errcontext=[]){
    static $codeLabels;

    if( strpos($errstr, \Oscar\Exception\OscarException::ACCOUNT_DISABLED) ){
        $err = "Votre compte a été désactivé";
        require __DIR__.'/error.php';
        die();
    }
    if( $codeLabels === null ){
        $codeLabels = [
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_ERROR => 'ERROR',

            E_CORE_ERROR => 'ERROR',
            E_CORE_WARNING => 'WARNING',
            E_CORE_ERROR => 'ERROR',

            E_USER_NOTICE => 'NOTICE',
            E_USER_DEPRECATED => 'DEPRECATED',
            E_USER_ERROR => 'ERROR',

            E_DEPRECATED => 'DEPRECATED',
            E_STRICT => 'STRICT',
        ];
    }

    if( $codeLabels[$errno] ){
        $codeStr = $codeLabels[$errno];
    } else {

        $codeStr = 'UNKNOW:'.$errno;
    }

    $msg = sprintf("[%s] %s (%s, ligne %s)", $codeStr, $errstr, $errfile, $errline);

    if (!(error_reporting() & $errno)) {
        // Ce code d'erreur n'est pas inclus dans error_reporting(), donc il continue
        // jusqu'au gestionaire d'erreur standard de PHP
        return;
    }

    error_log($msg);

    if($codeStr == 'ERROR'){
        $errorDisplayed = "Une erreur est survenue...";
        if( DEBUG_OSCAR ){
            $errorDisplayed = $msg;
        }
        if( array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) ){
            echo $msg;
        } else {
            ?>
            <div class="container">
                <section class="alert alert-danger">
                    <h1>
                        Erreur d'execution PHP : <?= $msg ?>
                    </h1>

                    <pre><?= $errorDisplayed ?></pre>
                    <p>
                        <small>Vous pouvez transmettre ce message à l'administateur Oscar pour l'aider à résoudre le
                            problème.
                        </small>
                    </p>
                </section>
            </div>


            <?php
        }
        exit(1);
    }

    return true;

    /*switch ($errno) {
        case E_USER_ERROR:
            echo "<b>Mon ERREUR</b> [$errno] $errstr<br />\n";
            echo "  Erreur fatale sur la ligne $errline dans le fichier $errfile";
            echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            echo "Arrêt...<br />\n";
            exit(1);
            break;

        case E_USER_WARNING:
            //error_log()
            echo "<b>Mon ALERTE</b> [$errno] $errstr<br />\n";
            break;

        case E_USER_NOTICE:
            echo "<b>Mon AVERTISSEMENT</b> [$errno] $errstr<br />\n";
            break;

        default:
            echo "Type d'erreur inconnu : [$errno] $errstr<br />\n";
            break;
    }*/

    /* Ne pas exécuter le gestionnaire interne de PHP */
    // return true;
}


register_shutdown_function( "fatal_handler" );

function fatal_handler() {
    $error = error_get_last();

    if( $error !== NULL) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = "Fatal error : " . $error["message"];

        return oscar_exception( $errno, $errstr, $errfile, $errline);
    }
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


$app = \Laminas\Mvc\Application::init(require 'config/application.config.php');
$app->run();
