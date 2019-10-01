<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:48
 * @copyright Certic (c) 2017
 */

namespace Oscar\Controller;

use Doctrine\ORM\NoResultException;
use Oscar\Entity\Activity;
use Oscar\Entity\Authentification;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\Privilege;
use Oscar\Exception\OscarException;
use Oscar\Form\NotificationForm;
use Oscar\Provider\Privileges;
use Oscar\Service\NotificationService;
use Zend\View\Model\JsonModel;

/**
 * Class NotificationController
 * @package Oscar\Controller
 */
class NotificationController extends AbstractOscarController
{
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /** @var NotificationService */
    private $notificationService;

    /**
     * @return NotificationService
     */
    public function getNotificationService(): NotificationService
    {
        return $this->notificationService;
    }

    /**
     * @param NotificationService $notificationService
     */
    public function setNotificationService(NotificationService $notificationService): void
    {
        $this->notificationService = $notificationService;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function historyAction()
    {
        $personId = $this->getOscarUserContextService()->getCurrentPerson()->getId();
        try {
            return [
                'notifications' => $this->getNotificationService()->getAllNotificationsPerson($personId)
            ];
        } catch (\Exception $e ){
            $this->getLoggerService()->error("notifications > history : " . $e->getMessage());
            return $this->getResponseInternalError(sprintf("Impossible de charger l'historiques des notifications : %s", $e->getMessage()));
        }
    }

    public function indexAction()
    {
        if( !$this->getOscarUserContextService()->getCurrentPerson() ){
            return [];
        }
        // $this->getOscarUserContext()->check(Privileges::PERSON_NOTIFICATION_MENU);
        $personId = $this->getOscarUserContextService()->getCurrentPerson()->getId();

        /** @var NotificationService $notificationService */
        $notificationService = $this->getNotificationService();


        if ($this->getHttpXMethod() == "DELETE") {
            $ids = $this->params()->fromQuery('ids');
            if( $ids ){
                $ids = explode(',',$ids);
                try {
                    $notificationService->deleteNotificationsPersonById($ids, $this->getOscarUserContextService()->getCurrentPerson());
                    return $this->getResponseOk('Notifications supprimées');
                } catch( \Exception $e ){
                    return $this->getResponseInternalError($e->getMessage());
                }

            }
            return $this->getResponseInternalError("Impossible de traiter la demande");
        }

        try {
            $notifications = $notificationService->getNotificationsPerson($personId, true);
        } catch (\Exception $e ){
            return $this->getResponseInternalError($e->getMessage()." - " . $e->getTraceAsString());
        }


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
        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_NOTIFICATION_PERSON);
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
                $serviceNotification->notification(
                    $message,
                    [$person],
                    'Application',
                    -1,
                    'free-notification',
                    new \DateTime(),
                    new \DateTime()

                );
                die("OK");
            }

        } else {

        }
        return [
            'person' => $person,
            'form' => $form
        ];
    }
}