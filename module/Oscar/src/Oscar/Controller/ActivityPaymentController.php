<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/11/15 10:52
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPayment;
use Oscar\Exception\OscarException;
use Oscar\Form\ActivityPaymentForm;
use Oscar\Provider\Privileges;
use Oscar\Service\ActivityPaymentService;
use Oscar\Service\NotificationService;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantApiService;
use Oscar\Service\ProjectGrantService;
use Laminas\Http\Request;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;

class ActivityPaymentController extends AbstractOscarController implements UseServiceContainer
{
    use UseServiceContainerTrait;

    /////////////////////////////////////////////////////////////////////////////////////////////////////////// SERVICES
    /** @var ProjectGrantService */
    private $projectGrantService;

    /** @var NotificationService */
    private $notificationService;

    /** @var PersonService */
    private $personService;

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

    /**
     * @return PersonService
     */
    public function getPersonService(): PersonService
    {
        return $this->personService;
    }

    /**
     * @param PersonService $personService
     */
    public function setPersonService(PersonService $personService): void
    {
        $this->personService = $personService;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////// SERVICES


    /**
     * Retourne les versements à venir dans les 15 jours à venir.
     */
    public function incomeAction()
    {
        $payments = $this->getProjectGrantService()->getPaymentsIncoming();
        return [
            'payments' => $payments,
            'getDate'  => 'getDatePredicted'
        ];
    }

    /**
     * Liste des versements en retard.
     */
    public function lateAction()
    {
        $payments = $this->getProjectGrantService()->getPaymentsLate();
        return [
            'payments' => $payments,
            'getDate'  => 'getDatePredicted'
        ];
    }

    /**
     * Liste des versements de type "écart".
     */
    public function differenceAction()
    {
        $payments = $this->getProjectGrantService()->getPaymentsDifference();
        return [
            'payments' => $payments,
            'getDate'  => 'getDatePredicted'
        ];
    }


    /**
     * @return JsonModel
     */
    public function indexAction()
    {
        $idActivity = $this->params()->fromRoute('idactivity', null);
        $page = $this->params()->fromQuery('page', 1);

        // Appel avec une idActivity => appel ajax depuis la fiche détaillée de
        // l'activité.
        if ($idActivity) {
            $activity = $this->getProjectGrantService()->getActivityById($idActivity);
            $this->getOscarUserContextService()->check(Privileges::ACTIVITY_PAYMENT_SHOW, $activity);

            $method = $this->getHttpXMethod();

            if ($method != "GET" && !$this->getOscarUserContextService()->hasPrivileges(
                    Privileges::ACTIVITY_PAYMENT_MANAGE,
                    $activity
                )) {
                $this->getResponseBadRequest("Vous ne disposez pas des droits suffisants pour gérer les versements");
            }

            switch ($method) {
                case 'DELETE':
                    try {
                        /** @var ActivityPayment $payment */
                        $payment = $this->getProjectGrantService()->getActivityPaymentById(
                            $this->params()->fromQuery('id')
                        );
                        $this->getProjectGrantService()->deleteActivityPayment($payment);
                        return $this->getResponseOk("Le versement a bien été supprimé");
                    } catch (\Exception $e) {
                        $this->getLoggerService()->error($e->getTraceAsString());
                        return $this->getResponseInternalError(
                            sprintf(_("Impossible de supprimer le payment : %s"), $e->getMessage())
                        );
                    }


                case 'PUT':
                    return $this->getResponseDeprecated();


                case 'POST':
                    $action = $this->params()->fromPost('action');

                    $postedDatas = [
                        'amount'          => $this->params()->fromPost('amount'),
                        'activity'        => $activity,
                        'comment'         => $this->params()->fromPost('comment'),
                        'codeTransaction' => $this->params()->fromPost('codeTransaction'),
                        'currencyId'      => $this->params()->fromPost('currencyId'),
                        'status'          => $this->params()->fromPost('status'),
                        'rate'            => $this->params()->fromPost('rate'),
                        'datePredicted'   => $this->params()->fromPost('datePredicted'),
                        'datePayment'     => $this->params()->fromPost('datePayment'),
                    ];


                    if ($action == 'create') {
                        try {
                            $this->getProjectGrantService()->addNewActivityPayment($postedDatas);
                            return $this->getResponseOk("Le versement a bien été ajouté");
                        } catch (\Exception $e) {
                            $this->getLoggerService()->error($e->getTraceAsString());
                            return $this->getResponseInternalError(
                                sprintf(_("Impossible d'ajouter le payment : %s"), $e->getMessage())
                            );
                        }
                    }

                    elseif ($action == 'update') {
                        try {
                            $postedDatas['id'] = $this->params()->fromPost('id');
                            $this->getProjectGrantService()->updateActivityPayment($postedDatas);
                            return $this->getResponseOk("Le versement a bien été modifié");
                        } catch (\Exception $e) {
                            return $this->getResponseInternalError(
                                sprintf(_("Impossible de modifier le payment : %s"), $e->getMessage())
                            );
                        }
                    }

                    else {
                        return $this->getResponseBadRequest('Action inconnue');
                    }
            }

            $view = new JsonModel($this->getProjectGrantService()->getListActivityPaymentByActivity($activity));

            return $view;
        }

        // Page "Liste"
        else {
            $search = $this->params()->fromQuery('q', '');
            return $this->getProjectGrantService()->getListActivityPayment($search, $page);
        }
    }

    public function restAction()
    {
        // Deprecated
    }

    /**
     * @return ActivityPaymentService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getActivityPaymentService(): ActivityPaymentService
    {
        return $this->getServiceContainer()->get(ActivityPaymentService::class);
    }

    public function indexRestAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        $activity = $this->getProjectGrantService()->getActivityById($this->params()->fromRoute('idactivity', null));

        $method = $request->getMethod();

        try {
            switch ($method) {
                case 'GET':
                    /** @var ProjectGrantApiService $projectGrantApiService */
                    $projectGrantApiService = $this->getServiceContainer()->get(ProjectGrantApiService::class);
                    $data = $projectGrantApiService->getActivityJson(
                        $activity->getId(),
                        $this->url(),
                        $this->getOscarUserContextService(),
                        'payments'
                    );
                    return $this->jsonOutput($data);

                case 'DELETE':
                    $this->getActivityPaymentService()->deletePayment($this->getActivityPaymentFromQuery());
                    return $this->getResponseOk("Versement supprimé");

                case 'PUT':
                    $this->getActivityPaymentService()->createPayment($this->getJsonREST(), $activity);
                    return $this->getResponseOk("Versement ajouté");

                case 'POST':
                    $this->getActivityPaymentService()->updatePayment($this->getJsonREST(), $activity);
                    return $this->getResponseOk("Versement modifié");

                default:
                    throw new \Exception("Action inconnue");
            }
        } catch (\Exception $exception) {
            return $this->jsonError($exception->getMessage());
        }
    }

    protected function getActivityPaymentFromQuery( string $field = 'id') :ActivityPayment
    {
       $id = $this->params()->fromQuery($field, null);
       if( $id == null ){
           throw new \Exception("ID obligatoire");
       }

       return $this->getActivityPaymentService()->getPaymentById($id);
    }

    public function changeAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $response = new JsonModel();

        /** @var ActivityPayment $entity */
        $entity = $this->getProjectGrantService()->getActivityPayment($this->params()->fromRoute('id'));

        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_PAYMENT_MANAGE, $entity->getActivity());

        if ($request->getMethod() === "DELETE") {
            throw new \Exception("DEPRECATED");
        }
        else {
            /** @var ActivityPaymentForm $form */
            $form = new ActivityPaymentForm();
            $form->setAttribute(
                'action',
                $this->url()->fromRoute(
                    null,
                    ['idactivity' => $entity->getActivity()->getId(), 'id' => $entity->getId()]
                )
            );
            $form->setProjectGrantService($this->getProjectGrantService());
            $form->init();
            $form->bind($entity);
            if ($request->isPost()) {
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    $entity->getActivity()->touch();

                    $this->getProjectGrantService()->getEntityManager()->flush();
                    $this->getActivityLogService()->addUserInfo(
                        sprintf(" a modifié le %s dans l'activité %s", $entity, $entity->getActivity()->log()),
                        'Activity',
                        $entity->getActivity()->getId()
                    );
                    $this->redirect()->toRoute('payment');
                }
            }

            $view = new ViewModel([
                                      'payment'  => $entity,
                                      'activity' => $entity->getActivity(),
                                      'form'     => $form,
                                  ]);
            if ($request->isXmlHttpRequest()) {
                $view->setTerminal(true);
            }
            $view->setTemplate('oscar/activity-payment/form.phtml');

            return $view;
        }

        die('<div class="alert alert-danger">' . $request->getMethod() . ' not implemented</div>');
    }
}