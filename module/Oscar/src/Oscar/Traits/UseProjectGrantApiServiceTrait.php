<?php
namespace Oscar\Traits;

use Oscar\Service\ProjectGrantApiService;

trait UseProjectGrantApiServiceTrait
{
    /**
     * @var ProjectGrantApiService
     */
    private $projectGrantApiService;

    /**
     * @param ProjectGrantApiService $projectGrantApiService
     */
    public function setProjectGrantApiService( ProjectGrantApiService $projectGrantApiService ) :void
    {
        $this->projectGrantApiService = $projectGrantApiService;
    }

    /**
     * @return ProjectGrantApiService
     */
    public function getProjectGrantApiService() :ProjectGrantApiService {
        return $this->projectGrantApiService;
    }
}