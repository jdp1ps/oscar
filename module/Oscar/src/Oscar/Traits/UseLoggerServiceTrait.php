<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;

use Monolog\Logger;
use Oscar\Service\LoggerService;

trait UseLoggerServiceTrait
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $s
     */
    public function setLoggerService( Logger $logger ) :void
    {
        $this->logger = $logger;
    }

    /**
     * @return LoggerService
     */
    public function getLoggerService() :Logger {
        return $this->logger;
    }
}