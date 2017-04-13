<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 28/05/15 12:21
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Service;

use Monolog\Logger;

class LoggerStdoutColor extends Logger
{
    private $COLOR = array(
        Logger::DEBUG => 'black',
        Logger::INFO => 'green',
        Logger::NOTICE => 'cyan',
        Logger::WARNING => 'yellow',

        // Fail
        Logger::ERROR => 'red',
        Logger::CRITICAL => 'red',
        Logger::ALERT => 'red',
        Logger::EMERGENCY => 'red',

    );
    /**
     *
     */
    public function addRecord($level, $message, array $context = array())
    {
        parent::addRecord($level, '[c='.$this->COLOR[$level].']'.date('Y-m-d H:i:s').' '.$message.'[/c]', $context);
    }
}
