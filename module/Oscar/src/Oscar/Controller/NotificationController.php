<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:48
 * @copyright Certic (c) 2017
 */

namespace Oscar\Controller;

/**
 * Class NotificationController
 * @package Oscar\Controller
 */
class NotificationController extends AbstractOscarController
{
    public function indexPersonAction()
    {
        $personId = $this->params()->fromRoute('idperson', null);
        if( $personId === null ){
            $personId = $this->getCurrentPerson()->getId();
        }
        var_dump($personId);
        die("Notification d'une personne");
    }
    public function indexAction()
    {
        $personId = $this->getCurrentPerson()->getId();
        $notificationService = $this->getServiceLocator()->get('NotificationService');
        $notifications = $notificationService->getNotificationsPerson($personId);
        var_dump($notifications);
        die("Notifications");
    }
}