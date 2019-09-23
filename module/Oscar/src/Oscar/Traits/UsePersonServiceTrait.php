<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;

use Oscar\Service\PersonService;

trait UsePersonServiceTrait
{
    /**
     * @var PersonService
     */
    private $personService;

    /**
     * @param PersonService $s
     */
    public function setPersonService( PersonService $personService ) :void
    {
        $this->personService = $personService;
    }

    /**
     * @return PersonService
     */
    public function getPersonService() :PersonService {
        return $this->personService;
    }
}