<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;

use Oscar\Service\ProjectGrantService;

trait UseProjectGrantServiceTrait
{
    /**
     * @var ProjectGrantService
     */
    private $projectGrantService;

    /**
     * @param ProjectGrantService $projectGrantService
     */
    public function setProjectGrantService( ProjectGrantService $projectGrantService ) :void
    {
        $this->projectGrantService = $projectGrantService;
    }

    /**
     * @return ProjectGrantService
     */
    public function getProjectGrantService() :ProjectGrantService {
        return $this->projectGrantService;
    }
}