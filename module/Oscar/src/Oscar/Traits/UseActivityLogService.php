<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:59
 */

namespace Oscar\Traits;

use Oscar\Service\ActivityLogService;

interface UseActivityLogService
{
    /**
     * @param ActivityLogService $em
     */
    public function setActivityLogService( ActivityLogService $em ) :void;

    /**
     * @return ActivityLogService
     */
    public function getActivityLogService() :ActivityLogService ;
}