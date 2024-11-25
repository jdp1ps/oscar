<?php


namespace Oscar\Service;


use Monolog\Logger;
use Oscar\Exception\OscarException;

class LoggerService extends Logger
{
    private string $currentFile = "";

    public function getCurrentFile(): string
    {
        return realpath($this->currentFile);
    }

    public function setCurrentFile(string $currentFile): self
    {
        $this->currentFile = $currentFile;
        return $this;
    }


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