<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;

use Oscar\Service\SpentService;

trait UseSpentServiceTrait
{
    /**
     * @var SpentService
     */
    private $spentService;

    /**
     * @param SpentService $spentService
     */
    public function setSpentService( SpentService $spentService ) :void
    {
        $this->spentService = $spentService;
    }

    /**
     * @return SpentService
     */
    public function getSpentService() :SpentService {
        return $this->spentService;
    }
}