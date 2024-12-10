<?php
namespace Oscar\Traits;


use Oscar\Service\ProjectGrantApiService;

interface UseProjectGrantApiService
{
    /**
     * @param PersonService $em
     */
    public function setProjectGrantApiService( ProjectGrantApiService $s ) :void;

    /**
     * @return PersonService
     */
    public function getProjectGrantApiService() :ProjectGrantApiService ;
}