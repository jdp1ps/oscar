<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;

use Oscar\Service\ActivityLogService;

trait UseActivityLogServiceTrait
{
    /**
     * @var ActivityLogService
     */
    private $activityLogService;

    /**
     * @param ActivityLogService $s
     */
    public function setActivityLogService( ActivityLogService $activityLogService ) :void
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * @return ActivityLogService
     */
    public function getActivityLogService() :ActivityLogService {
        return $this->activityLogService;
    }
}