<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:59
 */

namespace Oscar\Traits;

use Monolog\Logger;

interface UseLoggerService
{
    /**
     * @param Logger $logger
     */
    public function setLoggerService( Logger $logger ) :void;

    /**
     * @return Logger
     */
    public function getLoggerService() :Logger ;
}