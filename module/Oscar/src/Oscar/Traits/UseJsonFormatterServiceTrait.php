<?php
namespace Oscar\Traits;

use Monolog\Logger;
use Oscar\Service\JsonFormatterService;

trait UseJsonFormatterServiceTrait
{
    /**
     * @var JsonFormatterService
     */
    private $jsonFormatterService;

    /**
     * @param Logger $s
     */
    public function setJsonFormatterService( JsonFormatterService $jsonFormatterService ) :void
    {
        $this->jsonFormatterService = $jsonFormatterService;
    }

    /**
     * @return JsonFormatterService
     */
    public function getJsonFormatterService() :JsonFormatterService {
        return $this->jsonFormatterService;
    }
}