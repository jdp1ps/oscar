<?php
namespace Oscar\Traits;

use Oscar\Service\ProjectService;

trait UseProjectServiceTrait
{
    /**
     * @var ProjectService
     */
    private $projectService;

    /**
     * @param ProjectService $projectService
     */
    public function setProjectService( ProjectService $projectService ) :void
    {
        $this->projectService = $projectService;
    }

    /**
     * @return ProjectService
     */
    public function getProjectService() :ProjectService {
        return $this->projectService;
    }
}