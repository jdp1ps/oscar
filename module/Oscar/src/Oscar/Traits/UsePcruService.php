<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:59
 */

namespace Oscar\Traits;

use Oscar\Service\PCRUService;
use Oscar\Service\SpentService;

interface UsePcruService
{
    /**
     * @param PCRUService $sp
     */
    public function setPcruService( PcruService $sp ) :void;

    /**
     * @return PcruService
     */
    public function getPcruService() :PcruService ;
}