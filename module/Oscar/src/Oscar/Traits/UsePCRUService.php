<?php
namespace Oscar\Traits;

use Oscar\Service\PCRUService;

interface UsePCRUService
{
    /**
     * @param PCRUService $pcruService
     */
    public function setPCRUService( PCRUService $pcruService ) :void;

    /**
     * @return PCRUService
     */
    public function getPCRUService() :PCRUService ;
}