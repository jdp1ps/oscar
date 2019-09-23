<?php
namespace Oscar\Traits;

use Oscar\Service\PersonService;

interface UsePersonService
{
    /**
     * @param PersonService $em
     */
    public function setPersonService( PersonService $s ) :void;

    /**
     * @return PersonService
     */
    public function getPersonService() :PersonService ;
}