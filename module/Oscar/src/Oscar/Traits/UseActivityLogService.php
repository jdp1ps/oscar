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
     * @param EntityManager $em
     */
    public function setActivityLogService( ActivityLogService $em ) :void;

    /**
     * @return Entitymanager
     */
    public function getActivityLogService() :ActivityLogService ;
}