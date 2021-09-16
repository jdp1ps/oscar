<?php


namespace Oscar\Service;


use Monolog\Logger;
use Oscar\Exception\OscarException;

class LoggerService extends Logger
{
    /**
     * @param $error
     * @param string $class
     */
    public function throwLoggedError($error, $class = OscarException::class ) :void
    {
        $this->throwAdvancedLoggedError($error, "", $class);
    }

    /**
     * @param $error
     * @param string $class
     */
    public function throwAdvancedLoggedError($errorFront, $errorLogged, $class = OscarException::class ) :void
    {
        $this->error($errorFront . ($errorLogged ? " : " . $errorLogged : ""));
        if( $class != null ){
            throw new $class($errorFront);
        }
    }
}