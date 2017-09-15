<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:48
 * @copyright Certic (c) 2017
 */

namespace Oscar\Controller;

use Oscar\Form\NotificationForm;
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


        if ($this->getHttpXMethod() == "DELETE") {
            $ids = $this->params()->fromQuery('ids');
            if( $ids ){
                $ids = explode(',',$ids);
                try {
                    $notificationService->deleteNotifications($ids);
                    return $this->getResponseOk('Notifications supprimées');
                } catch( \Exception $e ){
                    return $this->getResponseInternalError($e->getMessage());
                }

            }
            return $this->getResponseInternalError("Impossible de traiter la demande");
        }

        $notifications = $notificationService->getNotificationsPerson($personId);


        $response = new JsonModel($notifications);
        return $response;
    }

    public function markReadAction()
    {
        $ids = $this->params()->fromQuery('ids', '');

    }

    public function deleteAction()
    {

    }

    public function notifyPersonAction()
    {
        $method = $this->getHttpXMethod();
        $form = new NotificationForm();
        $person = $this->getPersonService()->getPerson($this->params()->fromRoute('idperson'));
        $form->init();
        $request = $this->getRequest();
        if( $request->isPost() ){
            $form->setData($request->getPost());
            if( $form->isValid() ) {
                /** @var NotificationService $serviceNotification */
                $serviceNotification = $this->getServiceLocator()->get('NotificationService');
                $message = $form->get('message')->getValue();
                $serviceNotification->notification($message, [$person->getId()]);
                die();
            }

        } else {

        }
        return [
            'person' => $person,
            'form' => $form
        ];
    }
}