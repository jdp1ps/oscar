<?php
namespace Oscar\Traits;

use Oscar\Service\ActivityTypeService;

interface UseActivityTypeService
{
    /**
     * @param EntityManager $em
     */
    public function setActivityTypeService( ActivityTypeService $em ) :void;

    /**
     * @return Entitymanager
     */
    public function getActivityTypeService() :ActivityTypeService ;
}