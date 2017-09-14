<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:48
 * @copyright Certic (c) 2017
 */

namespace Oscar\Controller;

use Oscar\Service\NotificationService;
use Zend\View\Model\JsonModel;

/**
 * Class NotificationController
 * @package Oscar\Controller
 */
class NotificationController extends AbstractOscarController
{
    public function testAction()
    {
        $personsId = [13059, 13060];

        /** @var NotificationService $notificationService */
        $notificationService = $this->getServiceLocator()->get('NotificationService');


        $notificationService->notification("Test de notification", $personsId);

        die('Fini');
    }
    public function indexAction()
    {
        $personId = $this->getCurrentPerson()->getId();

        /** @var NotificationService $notificationService */
        $notificationService = $this->getServiceLocator()->get('NotificationService');

        $notifications = $notificationService->getNotificationsPerson($personId);

        $response = new JsonModel($notifications);
        return $response;

    }
}