<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:59
 */

namespace Oscar\Traits;

use Oscar\Service\ProjectGrantService;

interface UseActivityService
{
    /**
     * @param EntityManager $em
     */
    public function setActivityService( ProjectGrantService $em ) :void;

    /**
     * @return Entitymanager
     */
    public function getActivityService() :ProjectGrantService ;
}