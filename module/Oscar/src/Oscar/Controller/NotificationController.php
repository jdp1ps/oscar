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
    public function testAction()
    {
        $idActivity = 10090;

        /** @var Activity $activity */
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);
        echo "<h1>$activity</h1>\n";

        $persons = $this->getPersonService()->getAllPersonsWithPrivilegeInActivity(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity);
        foreach( $persons as $id=>$p ){
            echo sprintf('%s - [%s] %s <br>', $id, $p->getId(), $p);
        }

        die('Test');
    }


    public function historyAction()
    {
        $personId = $this->getCurrentPerson()->getId();

        /** @var NotificationService $notificationService */
        $notificationService = $this->getServiceLocator()->get('NotificationService');

        try {
            return [
                'notifications' => $notificationService->getAllNotificationsPerson($personId)
            ];
        } catch (\Exception $e ){
            return $this->getResponseInternalError($e->getMessage()." - " . $e->getTraceAsString());
        }
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
                    $notificationService->deleteNotificationsPersonById($ids, $this->getCurrentPerson());
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