<?php
namespace Oscar\Traits;

use Oscar\Service\NotificationService;

interface UseNotificationService
{
    /**
     * @param NotificationService $em
     */
    public function setNotificationService( NotificationService $s ) :void;

    /**
     * @return NotificationService
     */
    public function getNotificationService() :NotificationService ;
}