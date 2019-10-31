<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;

use Oscar\Service\ActivityTypeService;

trait UseActivityTypeServiceTrait
{
    /**
     * @var ActivityTypeService
     */
    private $activityTypeService;

    /**
     * @param ActivityTypeService $activityTypeService
     */
    public function setActivityTypeService( ActivityTypeService $activityTypeService ) :void
    {
        $this->activityTypeService = $activityTypeService;
    }

    /**
     * @return ActivityTypeService
     */
    public function getActivityTypeService() :ActivityTypeService {
        return $this->activityTypeService;
    }
}