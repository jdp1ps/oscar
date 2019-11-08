<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:59
 */

namespace Oscar\Traits;

use Oscar\Service\SpentService;

interface UseSpentService
{
    /**
     * @param SpentService $sp
     */
    public function setSpentService( SpentService $sp ) :void;

    /**
     * @return SpentService
     */
    public function getSpentService() :SpentService ;
}