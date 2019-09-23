<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;



use Oscar\Service\ProjectGrantService;

trait UseActivityServiceTrait
{
    /**
     * @var ProjectGrantService
     */
    private $activityService;

    /**
     * @param ActivityService $s
     */
    public function setActivityService( ProjectGrantService $activityService ) :void
    {
        $this->activityService = $activityService;
    }

    /**
     * @return ActivityService
     */
    public function getActivityService() :ProjectGrantService {
        return $this->activityService;
    }
}