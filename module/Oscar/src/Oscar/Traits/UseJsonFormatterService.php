<?php
namespace Oscar\Traits;

use Oscar\Service\JsonFormatterService;

interface UseJsonFormatterService
{
    public function setJsonFormatterService( JsonFormatterService $jsonFormatterService ) :void;

    /**
     * @return PCRUService
     */
    public function getJsonFormatterService() :JsonFormatterService ;
}