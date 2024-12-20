<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/11/15 10:52
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use BjyAuthorize\Exception\UnAuthorizedException;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Laminas\Http\Response;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\LogActivity;
use Oscar\Form\ActivityDateForm;
use Oscar\Provider\Privileges;
use Oscar\Service\MilestoneService;
use Oscar\Service\ProjectGrantApiService;
use Oscar\Service\ProjectGrantService;
use Laminas\Http\Request;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;

class ActivityDateController extends AbstractOscarController implements UseServiceContainer, UseLoggerService,
                                                                        UseOscarUserContextService
{

    use UseServiceContainerTrait, UseLoggerServiceTrait, UseOscarUserContextServiceTrait;

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

    //////////// ACTIVITY
    ///
    protected function milestonesActivityGet(Activity $activity): JsonModel
    {
        try {
            /** @var ProjectGrantApiService $serviceApi */
            $serviceApi = $this->getServiceContainer()->get(ProjectGrantApiService::class);

            $datas = $serviceApi->getActivityJson(
                $activity->getId(),
                $this->url(),
                $this->getOscarUserContextService(),
                'milestones'
            );

            return $this->jsonOutput($datas);
        } catch (\Exception $exception) {
            $this->getLoggerService()->throw(
                $exception,
                "Impossible de charger les jalons de l'activité $activity"
            );
        }
    }

    protected function milestonesActivityPut(Activity $activity): JsonModel|Response
    {
        $this->getLoggerService()->debug(__METHOD__);

        try {
            $datas = $this->getJsonREST();
            $idMilestone = $datas['id'];
            $milestone = $this->getMilestoneService()->getMilestone($idMilestone);
            if( !$milestone ) {
                return $this->jsonError("Le jalon $idMilestone n'existe pas");
            }

            $action = $datas['action'];
            if( !$action ){
                $this->getOscarUserContextService()->check(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity);
                if( $milestone->getActivity()->getId() != $activity->getId() ) {
                    return $this->jsonError("Accès transversal vers une autre activité");
                }
                $datas['type_id'] = $datas['type']['id'];
                $this->getMilestoneService()->updateFromArray(
                    $milestone,
                    $datas
                );
                return $this->getResponseOk("Jalon modifié");
            } else {
                if ($action == ActivityDate::PROGRESSION_VALID || $action == ActivityDate::PROGRESSION_UNVALID || $action == ActivityDate::PROGRESSION_INPROGRESS || $action == ActivityDate::PROGRESSION_CANCEL || $action == ActivityDate::PROGRESSION_REFUSED) {
                    $this->getOscarUserContextService()->check(
                        Privileges::ACTIVITY_MILESTONE_PROGRESSION,
                        $activity
                    );

                    $this->getActivityLogService()->addUserInfo(
                        sprintf(
                            "a modifié l'état du jalon %s dans  l'activité %s pour %s",
                            $milestone,
                            $milestone->getActivity()->log(),
                            $action
                        ),
                        'Activity',
                        $milestone->getActivity()->getId()
                    );

                    $milestone = $this->getMilestoneService()->setMilestoneProgression($milestone, $action);
                    return $this->ajaxResponse($milestone->toArray());
                } else {
                    return $this->jsonError("Action incohérente");
                }
            }

        } catch (\Exception $exception) {
            $this->getLoggerService()->throw(
                $exception,
                "Impossible de mettre à jour le jalon dans l'activité '$activity' : " . $exception->getMessage()
            );
        }
    }
    /**
     * Création d'un nouveau Jalon.
     *
     * @param Activity $activity
     * @return JsonModel|Response
     * @throws \Oscar\Exception\OscarException
     */
    protected function milestonesActivityPost(Activity $activity): JsonModel|Response
    {
        $this->getLoggerService()->debug(__METHOD__);
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity);
        try {
            $datas = $this->getJsonREST();
            $this->getMilestoneService()->createFromArray(
                [
                    'type_id'     => $datas['type']['id'],
                    'comment'     => $datas['comment'],
                    'dateStart'   => $datas['dateStart'],
                    'activity_id' => $activity->getId()
                ]
            );
            return $this->getResponseOk("Jalon créé");
        } catch (\Exception $exception) {
            $this->getLoggerService()->throw(
                $exception,
                "Impossible d'ajouter le jalon dans l'activité '$activity' : " . $exception->getMessage()
            );
        }
    }
    protected function milestonesActivityDelete(Activity $activity): JsonModel|Response
    {
        $this->getLoggerService()->debug(__METHOD__);
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity);
        try {
            $idMilestone = $this->getRequest()->getQuery('id');
            $milestone = $this->getMilestoneService()->getMilestone($idMilestone);
            if( !$milestone ) {
                return $this->jsonError("Le jalon $idMilestone n'existe pas");
            }

            $this->getMilestoneService()->deleteMilestone($milestone);

            return $this->getResponseOk("Jalon supprimé");
        } catch (\Exception $exception) {
            $this->getLoggerService()->throw(
                $exception,
                "Impossible de supprimer le jalon dans l'activité '$activity' : " . $exception->getMessage()
            );
        }
    }

    public function indexAction()
    {
        $activityId = $this->params()->fromRoute('idactivity');
        if ($activityId) {
            /** @var Activity $activity */
            $activity = $this->getProjectGrantService()->getActivityById($activityId);

            $method = $this->getRequest()->getMethod();
            switch ($method) {
                case 'GET':
                    return $this->milestonesActivityGet($activity);
                case 'POST':
                    return $this->milestonesActivityPost($activity);
                case 'PUT':
                    return $this->milestonesActivityPut($activity);
                case 'DELETE':
                    return $this->milestonesActivityDelete($activity);

                default:
                    return $this->jsonError("Accès incohérent");
            }
        }

        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_MILESTONE_SHOW);

        // Donnèes du GET
        $search = $this->params()->fromQuery('q', '');
        $periodStart = $this->params()->fromQuery('periodStart', "");
        $periodEnd = $this->params()->fromQuery('periodEnd', "");
        $typeId = $this->params()->fromQuery('typedate', '');

        // Datas
        $milestones = $this->getMilestoneService()->search($search, [
            'periodStart' => $periodStart,
            'periodEnd'   => $periodEnd,
            'type'        => $typeId
        ]);
        $typesDate = $this->getMilestoneService()->getMilestoneTypeForSelect();

        return [
            'milestones'       => $milestones,
            'search'           => $search,
            'periodStart'      => $periodStart,
            'periodEnd'        => $periodEnd,
            'filterType'       => $typeId,
            'filterTypeStates' => [],
            'typesDate'        => $typesDate,
        ];
    }

    /**
     * Gestion des Jalons (v 2.0)
     *
     * @return \Laminas\Http\Response|JsonModel
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
                'types'      => $types,
                'creatable'  => $this->getOscarUserContextService()->hasPrivileges(
                    Privileges::ACTIVITY_MILESTONE_MANAGE,
                    $activity
                )
            ];

            try {
                switch ($method) {
                    case 'DELETE':
                        $this->getOscarUserContextService()->hasPrivileges(
                            Privileges::ACTIVITY_MILESTONE_MANAGE,
                            $activity
                        );
                        $milestone = $this->getMilestoneService()->getMilestone($this->params()->fromQuery('id'));
                        $this->getMilestoneService()->deleteMilestoneById($milestone->getId());
                        return $this->getResponseOk("Jalon supprimé");
                        break;

                    case 'GET':
                        /** @var ProjectGrantApiService $serviceApi */
                        $serviceApi = $this->getServiceContainer()->get(ProjectGrantApiService::class);

                        return $this->jsonOutput(
                            $serviceApi->getActivityJson(
                                $activity->getId(),
                                $this->url(),
                                $this->getOscarUserContextService(),
                                'milestones'
                            )
                        );
                        // Default
                        break;

                    case 'POST':
                        $action = $this->params()->fromPost('action', 'update');


                        if ($action == 'create') {
                            throw new Missing404Exception();
                        }

                        $milestone = $this->getMilestoneService()->getMilestone($this->params()->fromPost('id'));

                        ////////////////////////////////////////////////////////////
                        // Marquer le jalon comme terminé / non-terminé
                        if ($action == ActivityDate::PROGRESSION_VALID || $action == ActivityDate::PROGRESSION_UNVALID || $action == ActivityDate::PROGRESSION_INPROGRESS || $action == ActivityDate::PROGRESSION_CANCEL || $action == ActivityDate::PROGRESSION_REFUSED) {
                            $this->getOscarUserContextService()->check(
                                Privileges::ACTIVITY_MILESTONE_PROGRESSION,
                                $activity
                            );

                            $this->getActivityLogService()->addUserInfo(
                                sprintf(
                                    "a modifié l'état du jalon %s dans  l'activité %s pour %s",
                                    $milestone,
                                    $milestone->getActivity()->log(),
                                    $action
                                ),
                                'Activity',
                                $milestone->getActivity()->getId()
                            );

                            $milestone = $this->getMilestoneService()->setMilestoneProgression($milestone, $action);
                            return $this->ajaxResponse($milestone->toArray());
                        } // Mise à jour
                        else {
                            if ($action == 'update') {
                                $this->getOscarUserContextService()->check(
                                    Privileges::ACTIVITY_MILESTONE_MANAGE,
                                    $activity
                                );
                                $typeId = $this->params()->fromPost('type');
                                $comment = $this->params()->fromPost('comment');
                                $date = $this->params()->fromPost('dateStart');

                                $milestone = $this->getMilestoneService()->updateFromArray($milestone, [
                                    'type_id'   => $typeId,
                                    'comment'   => $comment,
                                    'dateStart' => $date,
                                ]);

                                $this->getActivityLogService()->addUserInfo(
                                    sprintf(
                                        "a modifié le jalon %s dans  l'activité %s",
                                        $milestone,
                                        $milestone->getActivity()->log()
                                    ),
                                    'Activity',
                                    $milestone->getActivity()->getId()
                                );

                                return $this->ajaxResponse($milestone->toArray());
                            }
                            else {
                                return $this->getResponseBadRequest("L'action $action action n'est pas supportée.");
                            }
                        }
                        break;
                    default:
                        return $this->getResponseBadRequest("Protocol bullshit");
                }
            } catch (\Exception $e) {
                return $this->getResponseInternalError($e->getMessage());
            }

            $view = new JsonModel($data);

            return $view;
        } catch (UnAuthorizedException $e) {
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
                    sprintf(
                        "a supprimé le jalon %s dans  l'activité %s",
                        $activityDate,
                        $activityDate->getActivity()->log()
                    ),
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
        }
        else {
            $form = new ActivityDateForm();
            $idActivity = $this->params()->fromRoute('idactivity');
            $activity = $this->getProjectGrantService()->getActivityById($idActivity);
            $form->setProjectGrantService($this->getProjectGrantService());

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
                                      'title'    => 'Modification du jalon',
                                      'message'  => $message,
                                      'activity' => $activity,
                                      'form'     => $form,
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

        $form->setProjectGrantService($this->getProjectGrantService());
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
                                  'title'    => 'Nouveau jalon',
                                  'activity' => $activity,
                                  'form'     => $form,
                              ]);

        if ($request->isXmlHttpRequest()) {
            $view->setTerminal(true);
        }

        $view->setTemplate('oscar/activity-date/form.phtml');

        return $view;
    }
}