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
use Oscar\Entity\LogActivity;
use Oscar\Form\ActivityDateForm;
use Oscar\Form\ActivityTypeForm;
use Oscar\Provider\Privileges;
use Oscar\Service\MilestoneService;
use Oscar\Service\NotificationService;
use Oscar\Service\ProjectGrantService;
use Zend\Http\Request;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ActivityDateController extends AbstractOscarController
{
    /** @var ProjectGrantService */
    private $projectGrantService;

    /** @var MilestoneService */
    private $milestoneService;

    /**
     * @return ProjectGrantService
     */
    public function getProjectGrantService(): ProjectGrantService
    {
        return $this->projectGrantService;
    }

    /**
     * @param ProjectGrantService $projectGrantService
     */
    public function setProjectGrantService(ProjectGrantService $projectGrantService): void
    {
        $this->projectGrantService = $projectGrantService;
    }

    /**
     * @return MilestoneService
     */
    public function getMilestoneService(): MilestoneService
    {
        return $this->milestoneService;
    }

    /**
     * @param MilestoneService $milestoneService
     */
    public function setMilestoneService(MilestoneService $milestoneService): void
    {
        $this->milestoneService = $milestoneService;
    }

    public function indexAction(){
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_MILESTONE_SHOW);
        return [
            'milestones' => $this->getMilestoneService()->getMilestones()
        ];
    }

    /**
     * Gestion des Jalons (v 2.0)
     *
     * @return \Zend\Http\Response|JsonModel
     */
    public function activityAction()
    {
        try {
            $activity = $this->getProjectGrantService()->getActivityById($this->params()->fromRoute('idactivity'));
            $this->getOscarUserContextService()->check(Privileges::ACTIVITY_MILESTONE_SHOW, $activity);

            $method = $this->getHttpXMethod();

            $types = $this->getMilestoneService()->getMilestoneTypes('array');

            $milestones = array_values($this->getProjectGrantService()->getMilestones($activity->getId()));

            // Données envoyées
            $data = [
                'milestones' => $milestones,
                'types' => $types,
                'creatable' => $this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity)
            ];

            try {
                switch ($method) {
                    case 'DELETE':
                        $this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity);
                        $milestone = $this->getMilestoneService()->getMilestone($this->params()->fromQuery('id'));
                        $this->getMilestoneService()->deleteMilestoneById($milestone->getId());
                        $this->getActivityLogService()->addUserInfo(
                            sprintf("a supprimé le jalon %s dans  l'activité %s", $milestone, $milestone->getActivity()->log()),
                            'Activity',
                            $milestone->getActivity()->getId()
                        );
                        return $this->getResponseOk("Jalon supprimé");
                        break;

                    case 'GET':
                        // Default
                        break;

                    case 'POST':
                        $action = $this->params()->fromPost('action', 'update');

                        if( $action == 'create' ){
                            $this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity);
                            $milestone = $this->getMilestoneService()->createFromArray([
                                'type_id' => $_POST['type'],
                                'comment' => $_POST['comment'],
                                'dateStart' => $_POST['dateStart'],
                                'activity_id' => $activity->getId(),
                            ]);
                            $this->getActivityLogService()->addUserInfo(
                                sprintf("a ajouté le jalon %s dans  l'activité %s", $milestone, $milestone->getActivity()->log()),
                                'Activity',
                                $milestone->getActivity()->getId()
                            );
                            return $this->ajaxResponse($milestone->toArray());
                        }

                        $milestone = $this->getMilestoneService()->getMilestone($this->params()->fromPost('id'));

                        ////////////////////////////////////////////////////////////
                        // Marquer le jalon comme terminé / non-terminé
                        if ($action == 'valid' || $action == 'unvalid' || $action == 'inprogress') {
                            $this->getOscarUserContextService()->check(Privileges::ACTIVITY_MILESTONE_PROGRESSION, $activity);

                            $this->getActivityLogService()->addUserInfo(
                                sprintf("a modifié l'état du jalon %s dans  l'activité %s pour %s", $milestone, $milestone->getActivity()->log(), $action),
                                'Activity',
                                $milestone->getActivity()->getId()
                            );

                            $milestone = $this->getMilestoneService()->setMilestoneProgression($milestone, $action);
                            return $this->ajaxResponse($milestone->toArray());

                        } // Mise à jour
                        else if ($action == 'update') {

                            $this->getOscarUserContextService()->check(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity);
                            $typeId = $this->params()->fromPost('type');
                            $comment = $this->params()->fromPost('comment');
                            $date = $this->params()->fromPost('dateStart');

                            $milestone = $this->getMilestoneService()->updateFromArray($milestone, [
                                'type_id' => $typeId,
                                'comment' => $comment,
                                'dateStart' => $date,
                            ]);

                            $this->getActivityLogService()->addUserInfo(
                                sprintf("a modifié le jalon %s dans  l'activité %s", $milestone, $milestone->getActivity()->log()),
                                'Activity',
                                $milestone->getActivity()->getId()
                            );

                            return $this->ajaxResponse($milestone->toArray());
                        } else {
                            return $this->getResponseBadRequest("Cette action n'est pas supportée.");
                        }
                        break;
                    default:
                        return $this->getResponseBadRequest("Protocol bullshit");

                }
            } catch (\Exception $e ){
                return $this->getResponseInternalError($e->getMessage());
            }

            $view = new JsonModel($data);

            return $view;

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
        $activityDate = $this->getProjectGrantService()->getActivityDate($this->params()->fromRoute('id'));

        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_MILESTONE_MANAGE, $activityDate->getActivity());


        if ($request->getMethod() === "DELETE") {
            try {
                $activityDate->getActivity()->touch();
                $this->getProjectGrantService()->deleteActivityDate($activityDate);
                $this->getActivityLogService()->addUserInfo(
                    sprintf("a supprimé le jalon %s dans  l'activité %s", $activityDate, $activityDate->getActivity()->log()),
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