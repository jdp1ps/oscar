<?php
namespace Oscar\Traits;


use Oscar\Service\ProjectService;

interface UseProjectService
{
    /**
     * @param PersonService $em
     */
    public function setProjectService( ProjectService $s ) :void;

    /**
     * @return PersonService
     */
    public function getProjectService() :ProjectService ;
}