<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;

use Oscar\Service\PcruService;

trait UsePcruServiceTrait
{
    /**
     * @var PcruService
     */
    private $personService;

    /**
     * @param PcruService $s
     */
    public function setPcruService( PcruService $personService ) :void
    {
        $this->personService = $personService;
    }

    /**
     * @return PcruService
     */
    public function getPcruService() :PcruService {
        return $this->personService;
    }
}