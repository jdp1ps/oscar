<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/11/15 10:52
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use BjyAuthorize\Exception\UnAuthorizedException;
use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityType;
use Oscar\Entity\DateType;
use Oscar\Form\ActivityDateForm;
use Oscar\Form\ActivityTypeForm;
use Oscar\Provider\Privileges;
use Oscar\Service\NotificationService;
use Zend\Http\Request;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ActivityDateController extends AbstractOscarController
{
    public function indexAction()
    {
        $idActivity = $this->params()->fromRoute('idactivity');
        if ($idActivity) {
            $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);
            $this->getOscarUserContext()->check(Privileges::ACTIVITY_MILESTONE_SHOW, $activity);
            $view = new JsonModel(array_values($this->getActivityService()->getMilestones($idActivity)));
            return $view;
        } else {
            $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('m')
                ->from(ActivityDate::class, 'm')
                ->orderBy('m.dateStart', 'DESC');
            return [
                'milestones' => $qb->getQuery()->getResult()
            ];
        }
    }

    /**
     * Gestion des Jalons (v 2.0)
     *
     * @return \Zend\Http\Response|JsonModel
     */
    public function activityAction()
    {
        try {
            $idActivity = $this->params()->fromRoute('idactivity');
            $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);
            $this->getOscarUserContext()->check(Privileges::ACTIVITY_MILESTONE_SHOW, $activity);

            if ($idActivity) {

                $method = $this->getHttpXMethod();

                $types = $this->getEntityManager()->createQueryBuilder()
                    ->select('t')
                    ->from(DateType::class, 't')
                    ->getQuery()
                    ->getResult(Query::HYDRATE_ARRAY);

                $milestones = array_values($this->getActivityService()->getMilestones($idActivity));

                // Données envoyées
                $data = [
                    'milestones' => $milestones,
                    'types' => $types,
                    'creatable' => $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity)
                ];

                switch ($method) {
                    case 'DELETE':
                        $id = $this->params()->fromQuery('id');
                        $milestone = $this->getEntityManager()->getRepository(ActivityDate::class)->find($id);
                        /** @var NotificationService $notificationService */
                        $notificationService = $this->getServiceLocator()->get('NotificationService');

                        $notificationService->purgeNotificationMilestone($milestone);

                        $this->getEntityManager()->remove($milestone);
                        $this->getEntityManager()->flush();
                        return $this->getResponseOk("Jalon supprimé");
                        break;

                    case 'GET':

                        break;
                    case 'PUT':
                        $type = $this->getEntityManager()->getRepository(DateType::class)->find($_POST['type']);
                        $comment = $_POST['comment'];
                        $date = new \DateTime($_POST['dateStart']);

                        $milestone = new ActivityDate();
                        $this->getEntityManager()->persist($milestone);
                        $milestone->setDateStart($date)
                            ->setActivity($activity)
                            ->setComment($comment)
                            ->setType($type);
                        $this->getEntityManager()->flush($milestone);

                        /** @var NotificationService $notificationService */
                        $notificationService = $this->getServiceLocator()->get('NotificationService');

                        $notificationService->generateMilestoneNotifications($milestone);



                        return $this->ajaxResponse($milestone->toArray());


                        break;
                    case 'POST':
                        $action = $this->params()->fromPost('action', 'update');
                        $id = $this->params()->fromPost('id');

                        // Récupération du jalon
                        try {
                            /** @var ActivityDate $jalon */
                            $milestone = $this->getEntityManager()->getRepository(ActivityDate::class)->find($id);
                        } catch (\Exception $e) {
                            return $this->getResponseNotFound("Impossible de trouver ce jalon.");
                        }

                        ////////////////////////////////////////////////////////////
                        // Marquer le jalon comme terminé / non-terminé
                        if ($action == 'valid' || $action == 'unvalid' || $action == 'inprogress') {
                            $this->getOscarUserContext()->check(Privileges::ACTIVITY_MILESTONE_PROGRESSION, $activity);
                            if( $action == 'valid' )
                                $milestone->setFinished(ActivityDate::FINISH_VALUE);

                            else if ($action == 'unvalid')
                                $milestone->setFinished(0);

                            else
                                $milestone->setFinished(50);


                        } // Mise à jour
                        else if ($action == 'update') {
                            $this->getOscarUserContext()->check(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity);
                            $typeId = $this->params()->fromPost('type');
                            $comment = $this->params()->fromPost('comment');
                            $date = new \DateTime($this->params()->fromPost('dateStart'));

                            try {
                                $type = $this->getEntityManager()->getRepository(DateType::class)->find($typeId);
                                $milestone->setDateStart($date)
                                    ->setActivity($activity)
                                    ->setComment($comment)
                                    ->setType($type);

                            } catch (\Exception $e) {
                                return $this->getResponseNotFound("Type de jalon non-trouvé.");
                            }
                        } else {
                            return $this->getResponseBadRequest("Cette action n'est pas supportée.");
                        }

                        $this->getEntityManager()->flush($milestone);

                        return $this->ajaxResponse($milestone->toArray());
                        break;
                    default:
                        return $this->getResponseBadRequest("Protocol bullshit");

                }

                $view = new JsonModel($data);

                return $view;
            } else {
                return $this->getResponseInternalError();
            }
        }
        catch( UnAuthorizedException $e ){
            return $this->getResponseBadRequest();
        }
    }

    public function changeAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $response = new JsonModel();

        /** @var ActivityDate $activityDate */
        $activityDate = $this->getActivityService()->getActivityDate($this->params()->fromRoute('id'));

        $this->getOscarUserContext()->check(Privileges::ACTIVITY_MILESTONE_MANAGE, $activityDate->getActivity());


        if ($request->getMethod() === "DELETE") {
            try {
                $activityDate->getActivity()->touch();
                $this->getActivityService()->deleteActivityDate($activityDate);
                $this->getActivityLogService()->addUserInfo(
                    sprintf("a supprimé la date %s dans  l'activité %s", $activityDate, $activityDate->getActivity()->log()),
                    'Activity',
                    $activityDate->getActivity()->getId()
                );
                $this->getEntityManager()->flush();
            } catch (\Exception $e) {
                $this->getResponse()->setStatusCode(500);
                $response->setVariable('error', 'Impossible de supprimer cette échéance');
            }
            return $response;
            throw new \Exception('Impossible de supprimer...');
        } else {
            $form = new ActivityDateForm();
            $idActivity = $this->params()->fromRoute('idactivity');
            $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);
            $form->setServiceLocator($this->getServiceLocator());

            $form->init();
            $form->bind($activityDate);
            $message = false;

            if ($request->isPost()) {
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $this->getEntityManager()->flush($activityDate);
                    $message = "Je jalon a bien été enregistré";
                }
            }

            $view = new ViewModel([
                'title' => 'Modification du jalon',
                'message' => $message,
                'activity' => $activity,
                'form' => $form,
            ]);

            if ($request->isXmlHttpRequest()) {
                $view->setTerminal(true);
            }

            $view->setTemplate('oscar/activity-date/form.phtml');

            return $view;
        }


        die('Traitement ' . $request->getMethod());
    }

    public function newAction()
    {
        $form = new ActivityDateForm();
        $idActivity = $this->params()->fromRoute('idactivity');

        /** @var Activity $activity */
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);
        $activityDate = new ActivityDate();
        $activityDate->setActivity($activity)
            ->setDateStart(new \DateTime());

        $form->setServiceLocator($this->getServiceLocator());
        $form->setObject($activityDate);
        $form->init();

        /** @var Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $this->getEntityManager()->persist($activityDate);
                $activity->touch();
                $this->getEntityManager()->flush();
                $this->getActivityLogService()->addUserInfo(
                    sprintf("a ajouté le jalon %s à l'activité %s", $activityDate, $activity->log()),
                    'Activity',
                    $activityDate->getActivity()->getId()
                );
                die('OK');
            }
        }

        $view = new ViewModel([
            'title' => 'Nouveau jalon',
            'activity' => $activity,
            'form' => $form,
        ]);

        if ($request->isXmlHttpRequest()) {
            $view->setTerminal(true);
        }

        $view->setTemplate('oscar/activity-date/form.phtml');

        return $view;
    }
}