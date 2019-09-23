<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;

use Oscar\Service\NotificationService;

trait UseNotificationServiceTrait
{
    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @param NotificationService $s
     */
    public function setNotificationService( NotificationService $notificationService ) :void
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @return NotificationService
     */
    public function getNotificationService() :NotificationService {
        return $this->notificationService;
    }
}